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
use Exception;
use OnMoon\OpenApiServerBundle\Exception\CannotParseOpenApi;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType as ObjectDefinition;
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

class SpecificationParser
{
    private ScalarTypesResolver $typeResolver;

    public function __construct(ScalarTypesResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
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
                    $requestBody = new ObjectDefinition(
                        $this->getPropertyGraph(
                            $requestSchema,
                            true,
                            true,
                            $exceptionContext + ['location' => 'request body']
                        )
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
     * @return ObjectDefinition[]
     */
    private function getResponseDtoDefinitions(
        $responses,
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

        /**
         * @var string $responseCode
         */
        foreach ($responses as $responseCode => $response) {
            $responseSchema = $this->findByMediaType($response, $specificationConfig->getMediaType());

            if ($responseSchema === null) {
                continue;
            }

            $propertyDefinitions                = $this->getPropertyGraph(
                $responseSchema,
                false,
                true,
                $exceptionContext + ['location' => 'response (code "' . $responseCode . '")']
            );
            $responseDefinition                 = new ObjectDefinition($propertyDefinitions);
            $responseDefinitions[$responseCode] = $responseDefinition;
        }

        return $responseDefinitions;
    }

    /**
     * @param RequestBody|Response|Reference|null $body
     */
    private function findByMediaType($body, string $mediaType): ?Schema
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
        /** @phpstan-ignore-next-line */
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
                ->getProperty($p->name, $p->schema, true, $exceptionContext, false)
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
     *
     * @return PropertyDefinition[]
     */
    private function getPropertyGraph(Schema $schema, bool $isRequest, bool $isRoot, array $exceptionContext): array
    {
        if ($isRoot && $schema->type !== Type::OBJECT) {
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
                throw new Exception('Property is not scheme');
            }

            if (($property->readOnly && $isRequest) || ($property->writeOnly && ! $isRequest)) {
                continue;
            }

            /**
             * @psalm-suppress RedundantConditionGivenDocblockType
             */
            $required              = is_array($schema->required) && in_array($propertyName, $schema->required);
            $propertyDefinitions[] = $this->getProperty($propertyName, $property, $isRequest, $exceptionContext)->setRequired($required);
        }

        return $propertyDefinitions;
    }

    /**
     * @param Schema|Reference|null                                       $property
     * @param array{location:string,method:string,url:string,path:string} $exceptionContext
     */
    private function getProperty(string $propertyName, $property, bool $isRequest, array $exceptionContext, bool $allowNonScalar = true): PropertyDefinition
    {
        if (! ($property instanceof Schema)) {
            throw new Exception('Property is not scheme');
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
            $objectType = new ObjectDefinition($this->getPropertyGraph($itemProperty, $isRequest, false, $exceptionContext));
            $propertyDefinition->setObjectTypeDefinition($objectType);
            $isScalar = false;
        } else {
            throw CannotParseOpenApi::becauseTypeNotSupported($propertyName, $itemProperty->type, $exceptionContext);
        }

        /** @var string|int|float|bool|null $schemaDefaultValue */
        $schemaDefaultValue = $itemProperty->default;

        if ($schemaDefaultValue !== null && $isScalar && $scalarTypeId !== null) {
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
