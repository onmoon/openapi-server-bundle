<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use OnMoon\OpenApiServerBundle\Exception\CannotParseOpenApi;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectSchema as ObjectDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation as OperationDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property as PropertyDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use Safe\DateTime;

use function array_filter;
use function array_key_exists;
use function array_map;
use function array_merge;
use function count;
use function in_array;
use function is_array;
use function is_int;
use function str_ends_with;
use function strcasecmp;
use function substr;

class SpecificationParser
{
    private ScalarTypesResolver $typeResolver;
    /** @var string[] */
    private array $skipHttpCodes;

    /** @param array<array-key, string|int> $skipHttpCodes */
    public function __construct(ScalarTypesResolver $typeResolver, array $skipHttpCodes)
    {
        $this->typeResolver  = $typeResolver;
        $this->skipHttpCodes = array_map(static fn ($code) => (string) $code, $skipHttpCodes);
    }

    public function parseOpenApi(string $specificationName, SpecificationConfig $specificationConfig, OpenApi $parsedSpecification): Specification
    {
        $operationDefinitions = [];
        /**
         * @var string $url
         */
        foreach ($parsedSpecification->paths as $url => $pathItem) {
            /**
             * @var string $method
             */
            foreach ($pathItem->getOperations() as $method => $operation) {
                $operationId = $operation->operationId;
                $summary     = $operation->summary;

                $exceptionContext = [
                    'url' => $url,
                    'method' => $method,
                    'path' => $specificationConfig->getPath(),
                ];

                if ($operationId === '') {
                    throw CannotParseOpenApi::becauseNoOperationIdSpecified($exceptionContext);
                }

                if (array_key_exists($operationId, $operationDefinitions)) {
                    throw CannotParseOpenApi::becauseDuplicateOperationId($operationId, $exceptionContext);
                }

                $responses = $this->getResponseDtoDefinitions($operation->responses, $specificationConfig, $exceptionContext);

                $requestSchema = $this->findByMediaType($operation->requestBody, $specificationConfig->getMediaType());
                $requestBody   = null;

                if ($requestSchema !== null) {
                    $requestBody = $this->getObjectDefinition(
                        $requestSchema,
                        true,
                        $exceptionContext + ['location' => 'request body']
                    );
                }

                $parameters        = $this->mergeParameters($pathItem, $operation);
                $requestParameters = [];

                foreach (['path', 'query'] as $in) {
                    $params = $this->parseParameters($in, $parameters, $exceptionContext + ['location' => 'request ' . $in . ' parameters']);

                    if ($params === null) {
                        continue;
                    }

                    $requestParameters[$in] = $params;
                }

                $handlerName = $specificationName . '.' . $operationId;

                $operationDefinitions[$operationId] = new OperationDefinition(
                    $url,
                    $method,
                    $handlerName,
                    $summary,
                    $requestBody,
                    $requestParameters,
                    $responses
                );
            }
        }

        return new Specification($operationDefinitions, $parsedSpecification);
    }

    /**
     * @param Response[]|Responses|null                                    $responses
     * @param array{location?:string,method:string,url:string,path:string} $exceptionContext
     *
     * @return array<string|int,ObjectDefinition>
     */
    private function getResponseDtoDefinitions(
        array|Responses|null $responses,
        SpecificationConfig $specificationConfig,
        array $exceptionContext
    ): array {
        $responseDefinitions = [];

        if ($responses === null) {
            return [];
        }

        if ($responses instanceof Responses) {
            $responses = $responses->getResponses();
        }

        foreach ($responses as $responseCode => $response) {
            if ($this->isHttpCodeSkipped((string) $responseCode)) {
                continue;
            }

            $responseSchema = $this->findByMediaType($response, $specificationConfig->getMediaType());

            if ($responseSchema === null) {
                $responseDefinitions[$responseCode] = new ObjectDefinition([]);
            } else {
                $responseDefinitions[$responseCode] = $this->getObjectDefinition(
                    $responseSchema,
                    false,
                    $exceptionContext + ['location' => 'response (code "' . (string) $responseCode . '")']
                );
            }
        }

        return $responseDefinitions;
    }

    private function isHttpCodeSkipped(string $code): bool
    {
        foreach ($this->skipHttpCodes as $skippedCode) {
            if (strcasecmp($skippedCode, $code) === 0) {
                return true;
            }

            if (
                str_ends_with($skippedCode, '**') &&
                strcasecmp(
                    substr($skippedCode, 0, -2),
                    substr($code, 0, -2)
                ) === 0
            ) {
                return true;
            }
        }

        return false;
    }

    private function findByMediaType(Response|RequestBody|Reference|null $body, string $mediaType): ?Schema
    {
        if ($body === null || $body instanceof Reference) {
            return null;
        }

        foreach ($body->content as $type => $data) {
            if ($type !== $mediaType || ! ($data instanceof MediaType)) {
                continue;
            }

            if ($data->schema instanceof Schema) {
                return $data->schema;
            }
        }

        return null;
    }

    /**
     * @param Parameter[]|Reference[] $parameters
     *
     * @return Parameter[]
     */
    private function filterParameters(array $parameters): array
    {
        return array_filter($parameters, static fn ($parameter): bool => $parameter instanceof Parameter);
    }

    /**
     * @return Parameter[]
     */
    private function mergeParameters(PathItem $pathItem, Operation $operation): array
    {
        $operationParameters = $this->filterParameters($operation->parameters);

        return array_merge(
            array_filter(
                $this->filterParameters($pathItem->parameters),
                static function (Parameter $pathParameter) use ($operationParameters): bool {
                    return count(
                        array_filter(
                            $operationParameters,
                            static function (Parameter $operationParameter) use ($pathParameter): bool {
                                    return $operationParameter->name === $pathParameter->name &&
                                        $operationParameter->in === $pathParameter->in;
                            }
                        )
                    ) === 0;
                }
            ),
            $operationParameters
        );
    }

    /**
     * @param Parameter[] $parameters
     *
     * @return Parameter[]
     */
    private function filterSupportedParameters(string $in, array $parameters): array
    {
        return array_filter($parameters, static fn ($parameter): bool => $parameter->in === $in);
    }

    /**
     * @param Parameter[]                                                 $parameters
     * @param array{location:string,method:string,url:string,path:string} $exceptionContext
     */
    private function parseParameters(string $in, array $parameters, array $exceptionContext): ?ObjectDefinition
    {
        $properties = array_map(
            fn (Parameter $p) => $this
                ->getProperty(
                    $p->name,
                    $p->schema,
                    // @codeCoverageIgnoreStart
                    true,
                    // @codeCoverageIgnoreEnd
                    $exceptionContext,
                    false
                )
                ->setRequired($p->required)
                ->setDescription($p->description),
            $this->filterSupportedParameters($in, $parameters)
        );

        if (count($properties) === 0) {
            return null;
        }

        return new ObjectDefinition($properties);
    }

    /**
     * @param array{location:string,method:string,url:string,path:string} $exceptionContext
     */
    private function getObjectDefinition(Schema $schema, bool $isRequest, array $exceptionContext): ObjectDefinition
    {
        if ($schema->type !== Type::OBJECT) {
            throw CannotParseOpenApi::becauseRootIsNotObject(
                $exceptionContext,
                ($schema->type === Type::ARRAY)
            );
        }

        $propertyDefinitions = [];
        /**
         * @var string $propertyName
         */
        foreach ($schema->properties as $propertyName => $property) {
            if (! ($property instanceof Schema)) {
                throw CannotParseOpenApi::becausePropertyIsNotScheme();
            }

            if (($property->readOnly && $isRequest) || ($property->writeOnly && ! $isRequest)) {
                continue;
            }

            /**
             * @psalm-suppress RedundantConditionGivenDocblockType
             */
            $required              = is_array($schema->required) && in_array($propertyName, $schema->required, true);
            $propertyDefinitions[] = $this->getProperty($propertyName, $property, $isRequest, $exceptionContext)->setRequired($required);
        }

        return new ObjectDefinition($propertyDefinitions);
    }

    /**
     * @param array{location:string,method:string,url:string,path:string} $exceptionContext
     */
    private function getProperty(
        string $propertyName,
        Schema|Reference|null $property,
        bool $isRequest,
        array $exceptionContext,
        bool $allowNonScalar = true
    ): PropertyDefinition {
        if (! ($property instanceof Schema)) {
            throw CannotParseOpenApi::becausePropertyIsNotScheme();
        }

        $propertyDefinition = new PropertyDefinition($propertyName);
        $propertyDefinition->setDescription($property->description);
        $propertyDefinition->setNullable($property->nullable);
        $propertyDefinition->setPattern($property->pattern);

        $scalarTypeId = null;
        $isScalar     = true;

        if ($property->type === Type::ARRAY) {
            if (! ($property->items instanceof Schema)) {
                throw CannotParseOpenApi::becauseArrayIsNotDescribed($propertyName, $exceptionContext);
            }

            $propertyDefinition->setArray(true);
            $itemProperty = $property->items;
            $isScalar     = false;
        } else {
            $itemProperty = $property;
        }

        if (Type::isScalar($itemProperty->type)) {
            $scalarTypeId = $this->typeResolver->findScalarType($itemProperty->type, $itemProperty->format);
            $propertyDefinition->setScalarTypeId($scalarTypeId);
        } elseif ($itemProperty->type === Type::OBJECT) {
            $objectType = $this->getObjectDefinition(
                $itemProperty,
                $isRequest,
                $exceptionContext
            );
            $propertyDefinition->setObjectTypeDefinition($objectType);
            $isScalar = false;
        } else {
            throw CannotParseOpenApi::becauseTypeNotSupported($propertyName, $itemProperty->type, $exceptionContext);
        }

        /** @var string|int|float|bool|null $schemaDefaultValue */
        $schemaDefaultValue = $itemProperty->default;

        if (
            // @codeCoverageIgnoreStart
            $schemaDefaultValue !== null &&
            $isScalar &&
            $scalarTypeId !== null
            // @codeCoverageIgnoreEnd
        ) {
            if ($this->typeResolver->isDateTime($scalarTypeId)) {
                // Symfony Yaml parses fields that looks like datetime into unix timestamp
                // however leaves strings untouched. We need to make types more solid
                if (is_int($schemaDefaultValue)) {
                    $datetime = (new DateTime())->setTimestamp($schemaDefaultValue);
                    /** @var string $schemaDefaultValue */
                    $schemaDefaultValue = $this->typeResolver->convert(false, $scalarTypeId, $datetime);
                }
            }

            $propertyDefinition->setDefaultValue($schemaDefaultValue);
        }

        if (! $isScalar && ! $allowNonScalar) {
            throw CannotParseOpenApi::becauseOnlyScalarAreAllowed($propertyName, $exceptionContext);
        }

        return $propertyDefinition;
    }
}
