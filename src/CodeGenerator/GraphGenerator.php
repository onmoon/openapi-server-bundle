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
use Lukasoppermann\Httpstatus\Httpstatus;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\CannotCreatePropertyName;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Exception\CannotGenerateCodeForOperation;
use OnMoon\OpenApiServerBundle\OpenApi\ScalarTypesResolver;
use OnMoon\OpenApiServerBundle\Specification\Specification;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;

class GraphGenerator
{
    private SpecificationLoader $loader;
    private Httpstatus $httpstatus;
    private NamingStrategy $namingStrategy;
    private ScalarTypesResolver $typeResolver;
    private string $rootNamespace;
    private string $rootPath;

    /**
     * GraphGenerator constructor.
     * @param SpecificationLoader $loader
     * @param NamingStrategy $namingStrategy
     * @param ScalarTypesResolver $typeResolver
     */
    public function __construct(SpecificationLoader $loader, NamingStrategy $namingStrategy, ScalarTypesResolver $typeResolver)
    {
        $this->loader = $loader;
        $this->namingStrategy = $namingStrategy;
        $this->typeResolver = $typeResolver;
    }


    public function generate() {
        $operations = [];
        foreach ($this->loader->list() as $specificationName => $specification) {
            $parsedSpecification = $this->loader->load($specificationName);

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

                    $op = [
                        $url,
                        $method,
                        $operationId,
                        $summary
                    ];

                    if ($operationId === '') {
                        throw CannotGenerateCodeForOperation::becauseNoOperationIdSpecified(
                            $url,
                            $method,
                            $specification->getPath()
                        );
                    }

                    $responses = $this->generateResponses($operation->responses, $specification, $url, $method);
                    $op[] = $responses;
                    $op[] = (count($responses) > 1);


                    $requestSchema = $this->findByMediaType($operation->requestBody, $specification->getMediaType());
                    if($requestSchema !== null) {
                        $op[] = $this->getPropertyGraph($requestSchema);
                    }
                    $parameters = $this->mergeParameters($pathItem, $operation);
                    $op[] = $this->parametersToPropertyDefinitions('path', $parameters);
                    $op[] = $this->parametersToPropertyDefinitions('query', $parameters);
                    $operations[] = $op;
                }
            }
        }
        return $operations;
    }

    private function generateResponses(?Responses $responses, Specification $specification, $url, $method) {
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

                    $responseDtoDefinitions[] = $this->getPropertyGraph($responseSchema);
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
     * @return PropertyDefinition[]
     */
    private function parametersToPropertyDefinitions(string $in, array $parameters) : array {
        return array_map(
            fn (Parameter $p) =>
                $this
                    ->getProperty($p->name, $p->schema)
                    ->setRequired($p->required),
            $this->filterSupportedParameters($in, $parameters)
        );
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
            $propertyDefinition= $this->getProperty($propertyName, $property);
            /**
             * @psalm-suppress RedundantConditionGivenDocblockType
             */
            $required = is_array($schema->required) && in_array($propertyName, $schema->required);
            $propertyDefinition->setRequired($required);

            $propertyDefinitions[] = $propertyDefinition;
        }

        return $propertyDefinitions;
    }

    private function getProperty(string $propertyName, Schema $property) : PropertyDefinition {
        if (! ($property instanceof Schema)) {
            throw new Exception('Property is not scheme');
        }

        $propertyDefinition = new PropertyDefinition($propertyName);

        $type         = null;
        $isScalar     = false;

        if ($property->type === Type::ARRAY) {
            if (! ($property->items instanceof Schema)) {
                throw new Exception('Array items must be described');
            }
            $propertyDefinition->setIsArray(true);
            $property = $property->items;
        }

        if (Type::isScalar($property->type)) {
            $typeId = $this->typeResolver->findScalarType($property);
            $propertyDefinition->setScalarTypeId($typeId);
            $isScalar = true;
        } elseif ($property->type === Type::OBJECT) {
            $type = $this->getPropertyGraph($property);
            $propertyDefinition->setObjectTypeDefinition($type);
        } else {
            throw new Exception('\''.$property->type.'\' type is not supported');
        }

        /** @var string|int|float|bool|null $schemaDefaultValue */
        $schemaDefaultValue = $property->default;
        if ($schemaDefaultValue !== null && $isScalar) {
            $propertyDefinition->setDefaultValue($schemaDefaultValue);
        }

        return $propertyDefinition;
    }
}
