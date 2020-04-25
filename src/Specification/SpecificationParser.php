<?php


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
use OnMoon\OpenApiServerBundle\Exception\CannotGenerateCodeForOperation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;

class SpecificationParser
{
    private ScalarTypesResolver $typeResolver;

    public function __construct(ScalarTypesResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }

    public function parseOpenApi(Specification $specification, OpenApi $parsedSpecification): SpecificationDefinition
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
                    'path' => $specification->getPath(),
                ];

                if ($operationId === '') {
                    throw CannotGenerateCodeForOperation::becauseNoOperationIdSpecified($exceptionContext);
                }
                if (array_key_exists($operationId, $operationDefinitions)) {
                    throw CannotGenerateCodeForOperation::becauseDuplicateOperationId($operationId, $exceptionContext);
                }

                $responses = $this->getResponseDtoDefinitions($operation->responses, $specification, $exceptionContext);

                $requestSchema = $this->findByMediaType($operation->requestBody, $specification->getMediaType());
                $requestBody   = null;
                if ($requestSchema !== null) {
                    $requestBody = new ObjectDefinition(
                        $this->getPropertyGraph(
                            $requestSchema,
                            array_merge($exceptionContext, ['location' => 'request body'])
                        )
                    );
                }

                $parameters             = $this->mergeParameters($pathItem, $operation);
                $requestParameters = [];
                foreach (['path', 'query'] as $in) {
                    $params = $this->parseParameters($in, $parameters, array_merge($exceptionContext, ['location' => 'request '.$in.' parameters']));
                    if($params !== null) {
                        $requestParameters[$in] = $params;
                    }
                }

                $operationDefinitions[$operationId] = new OperationDefinition(
                    $url,
                    $method,
                    $summary,
                    $requestBody,
                    $requestParameters,
                    $responses
                );
            }
        }

        return new SpecificationDefinition($operationDefinitions);
    }

    /**
     * @param Response[]|Responses|null $responses
     * @param string[]                  $exceptionContext
     *
     * @return ObjectDefinition[]
     */
    private function getResponseDtoDefinitions(
        $responses,
        Specification $specification,
        array $exceptionContext
    ) : array {
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
            $responseSchema = $this->findByMediaType($response, $specification->getMediaType());
            if ($responseSchema === null) {
                continue;
            }

            $exceptionContext = array_merge($exceptionContext, ['location' => 'response (code "' . $responseCode . '")']);
            if ($responseSchema->type !== Type::OBJECT) {
                throw CannotGenerateCodeForOperation::becauseRootIsNotObject(
                    $exceptionContext,
                    ($responseSchema->type === Type::ARRAY)
                );
            }

            $propertyDefinitions      = $this->getPropertyGraph($responseSchema, $exceptionContext);
            $responseDefinition       = new ObjectDefinition($propertyDefinitions);
            $responseDefinitions[$responseCode] = $responseDefinition;
        }

        return $responseDefinitions;
    }

    /**
     * @param RequestBody|Response|Reference|null $body
     */
    private function findByMediaType($body, string $mediaType) : ?Schema
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
    private function filterParameters(array $parameters) : array
    {
        /** @var Parameter[] $parameters */
        $parameters = array_filter($parameters, static fn ($parameter) : bool => $parameter instanceof Parameter);

        return $parameters;
    }

    /**
     * @return Parameter[]
     */
    private function mergeParameters(PathItem $pathItem, Operation $operation) : array
    {
        $operationParameters = $this->filterParameters($operation->parameters);

        return array_merge(
            array_filter(
                $this->filterParameters($pathItem->parameters),
                static function (Parameter $pathParameter) use ($operationParameters) : bool {
                    return count(
                            array_filter(
                                $operationParameters,
                                static function (Parameter $operationParameter) use ($pathParameter) : bool {
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
    private function filterSupportedParameters(string $in, array $parameters) : array
    {
        return array_filter($parameters, static fn ($parameter) : bool => $parameter->in === $in);
    }

    /**
     * @param Parameter[] $parameters
     * @param string[]    $exceptionContext
     */
    private function parseParameters(string $in, array $parameters, array $exceptionContext) : ?ObjectDefinition
    {
        $properties = array_map(
            fn (Parameter $p) =>
            $this
                ->getProperty($p->name, $p->schema, $exceptionContext, false)
                ->setRequired($p->required)
                ->setDescription($p->description),
            $this->filterSupportedParameters($in, $parameters)
        );

        if(count($properties) === 0) {
            return null;
        }

        return new ObjectDefinition($properties);
    }

    /**
     * @param string[] $exceptionContext
     *
     * @return PropertyDefinition[]
     */
    private function getPropertyGraph(Schema $schema, array $exceptionContext) : array
    {
        $propertyDefinitions = [];
        /**
         * @var string $propertyName
         */
        foreach ($schema->properties as $propertyName => $property) {
            /**
             * @psalm-suppress RedundantConditionGivenDocblockType
             */
            $required              = is_array($schema->required) && in_array($propertyName, $schema->required);
            $propertyDefinitions[] = $this->getProperty($propertyName, $property, $exceptionContext)->setRequired($required);
        }

        return $propertyDefinitions;
    }

    /**
     * @param Schema|Reference|null $property
     * @param string[]              $exceptionContext
     */
    private function getProperty(string $propertyName, $property, array $exceptionContext, bool $allowNonScalar = true) : PropertyDefinition
    {
        if (! ($property instanceof Schema)) {
            throw new Exception('Property is not scheme');
        }

        $propertyDefinition = new PropertyDefinition($propertyName);
        $propertyDefinition->setDescription($property->description);

        $type     = null;
        $isScalar = false;

        if ($property->type === Type::ARRAY) {
            if (! ($property->items instanceof Schema)) {
                throw CannotGenerateCodeForOperation::becauseArrayIsNotDescribed($propertyName, $exceptionContext);
            }

            $propertyDefinition->setArray(true);
            $property = $property->items;
        }

        if (Type::isScalar($property->type)) {
            $typeId = $this->typeResolver->findScalarType($property);
            $propertyDefinition->setScalarTypeId($typeId);
            $isScalar = true;
        } elseif ($property->type === Type::OBJECT) {
            $type = new ObjectDefinition($this->getPropertyGraph($property, $exceptionContext));
            $propertyDefinition->setObjectTypeDefinition($type);
        } else {
            throw CannotGenerateCodeForOperation::becauseTypeNotSupported($propertyName, $property->type, $exceptionContext);
        }

        /** @var string|int|float|bool|null $schemaDefaultValue */
        $schemaDefaultValue = $property->default;
        //ToDo: Support DateTime assignments
        if ($schemaDefaultValue !== null && $isScalar && ! class_exists($this->typeResolver->getPhpType($propertyDefinition->getScalarTypeId()??0))) {
            $propertyDefinition->setDefaultValue($schemaDefaultValue);
        }

        if (property_exists($property, 'pattern')) {
            $propertyDefinition->setPattern($property->pattern);
        }

        if (! $isScalar && ! $allowNonScalar) {
            throw CannotGenerateCodeForOperation::becauseOnlyScalarAreAllowed($propertyName, $exceptionContext);
        }

        return $propertyDefinition;
    }
}
