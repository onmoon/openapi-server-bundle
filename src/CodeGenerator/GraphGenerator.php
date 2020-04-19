<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use cebe\openapi\spec\MediaType;
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
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestParametersDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Exception\CannotGenerateCodeForOperation;
use OnMoon\OpenApiServerBundle\OpenApi\ScalarTypesResolver;
use OnMoon\OpenApiServerBundle\Specification\Specification;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;

class GraphGenerator
{
    private SpecificationLoader $loader;
    private ScalarTypesResolver $typeResolver;

    public function __construct(SpecificationLoader $loader, ScalarTypesResolver $typeResolver)
    {
        $this->loader = $loader;
        $this->typeResolver = $typeResolver;
    }

    /**
     * @return SpecificationDefinition[]
     */
    public function generate() : array {
        $specificationDefinitions = [];
        foreach ($this->loader->list() as $specificationName => $specification) {
            $parsedSpecification = $this->loader->load($specificationName);

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

                    if ($operationId === '') {
                        throw CannotGenerateCodeForOperation::becauseNoOperationIdSpecified(
                            $url,
                            $method,
                            $specification->getPath()
                        );
                    }

                    $responses = $this->getResponseDtoDefinitions($operation->responses, $specification, $url, $method);

                    $requestSchema = $this->findByMediaType($operation->requestBody, $specification->getMediaType());
                    $requestBody = null;
                    if($requestSchema !== null) {
                        $requestBody = new RequestBodyDtoDefinition($this->getPropertyGraph($requestSchema));
                    }
                    $parameters = $this->mergeParameters($pathItem, $operation);
                    $requestDefinitions = new RequestDtoDefinition(
                        $requestBody,
                        $this->parametersToDto('query', $parameters),
                        $this->parametersToDto('path', $parameters)
                    );
                    $operationDefinitions[] = new OperationDefinition(
                        $url,
                        $method,
                        $operationId,
                        $summary,
                        $requestDefinitions->isEmpty() ? null : $requestDefinitions,
                        $responses
                    );
                }
            }
            $specificationDefinitions[] = new SpecificationDefinition($specification, $operationDefinitions);
        }
        return $specificationDefinitions;
    }

    /**
     * @return ResponseDtoDefinition[]
     */
    private function getResponseDtoDefinitions(
        ?Responses $responses,
        Specification $specification,
        string $url,
        string $method
    ) : array
    {
        $responseDtoDefinitions = [];
        if ($responses instanceof Responses) {
            /**
             * @var string $responseCode
             */
            foreach ($responses->getResponses() as $responseCode => $response) {
                $responseSchema = $this->findByMediaType($response, $specification->getMediaType());
                if($responseSchema !== null) {
                    if ($responseSchema->type !== Type::OBJECT) {
                        throw CannotGenerateCodeForOperation::becauseRootIsNotObject(
                            $url,
                            $method,
                            'response (code "' . $responseCode . '")',
                            $specification->getPath(),
                            ($responseSchema->type === Type::ARRAY)
                        );
                    }
                    $propertyDefinitions = $this->getPropertyGraph($responseSchema);
                    $responseDefinition = new ResponseDtoDefinition($responseCode, $propertyDefinitions);
                    $responseDtoDefinitions[] = $responseDefinition;
                }
            }
        }
        return $responseDtoDefinitions;
    }

    /**
     * @param RequestBody|Response|Reference|null $body
     * @param string $mediaType
     * @return Schema|null
     */
    private function findByMediaType($body, string $mediaType): ?Schema {
        if (null === $body || $body instanceof Reference || null === $body->content) {
            return null;
        }

        foreach ($body->content as $type => $data) {
            if($type === $mediaType && $data instanceof MediaType) {
                if($data->schema instanceof Schema)
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
     * @return Parameter[]
     */
    private function filterSupportedParameters(string $in, array $parameters) : array
    {
        return array_filter($parameters, static fn ($parameter) : bool => $parameter->in === $in);
    }

    /**
     * @param Parameter[] $parameters
     * @return RequestParametersDtoDefinition|null
     */
    private function parametersToDto(string $in, array $parameters) : ?RequestParametersDtoDefinition {
        $properties = array_map(
            fn (Parameter $p) =>
                $this
                    ->getProperty($p->name, $p->schema, false)
                    ->setRequired($p->required)
                    ->setDescription($p->description),
            $this->filterSupportedParameters($in, $parameters)
        );

        if(count($properties) === 0) {
            return null;
        }

        return new RequestParametersDtoDefinition($properties);
    }

    /**
     * @return PropertyDefinition[]
     */
    private function getPropertyGraph(Schema $schema) : array {
        $propertyDefinitions = [];
        /**
         * @var string $propertyName
         */
        foreach ($schema->properties as $propertyName => $property) {
            $required = is_array($schema->required) && in_array($propertyName, $schema->required);
            $propertyDefinitions[] = $this->getProperty($propertyName, $property)->setRequired($required);
        }

        return $propertyDefinitions;
    }

    private function getProperty(string $propertyName, Schema $property, bool $allowNonScalar = true) : PropertyDefinition {
        if (! ($property instanceof Schema)) {
            throw new Exception('Property is not scheme');
        }

        $propertyDefinition = new PropertyDefinition($propertyName);
        $propertyDefinition->setDescription($property->description);

        $type         = null;
        $isScalar     = false;

        if ($property->type === Type::ARRAY) {
            if (! ($property->items instanceof Schema)) {
                throw new Exception('Array items must be described');
            }
            $propertyDefinition->setArray(true);
            $property = $property->items;
        }

        if (Type::isScalar($property->type)) {
            $typeId = $this->typeResolver->findScalarType($property);
            $propertyDefinition->setScalarTypeId($typeId);
            $isScalar = true;
        } elseif ($property->type === Type::OBJECT) {
            $type = new DtoDefinition($this->getPropertyGraph($property));
            $propertyDefinition->setObjectTypeDefinition($type);
        } else {
            throw new Exception('\''.$property->type.'\' type is not supported');
        }

        /** @var string|int|float|bool|null $schemaDefaultValue */
        $schemaDefaultValue = $property->default;
        if ($schemaDefaultValue !== null && $isScalar) {
            $propertyDefinition->setDefaultValue($schemaDefaultValue);
        }

        if (!$isScalar && !$allowNonScalar) {
            throw new Exception('Non scalar types are not allowed here');
        }

        return $propertyDefinition;
    }

}
