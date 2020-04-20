<?php

declare(strict_types=1);

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
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestParametersDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Exception\CannotGenerateCodeForOperation;
use OnMoon\OpenApiServerBundle\OpenApi\ScalarTypesResolver;
use OnMoon\OpenApiServerBundle\Specification\Specification;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use function array_filter;
use function array_map;
use function array_merge;
use function class_exists;
use function count;
use function in_array;
use function is_array;

class GraphGenerator
{
    private SpecificationLoader $loader;
    private ScalarTypesResolver $typeResolver;

    public function __construct(SpecificationLoader $loader, ScalarTypesResolver $typeResolver)
    {
        $this->loader       = $loader;
        $this->typeResolver = $typeResolver;
    }

    public function generateClassGraph() : GraphDefinition
    {
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

                    $exceptionContext = [
                        'url' => $url,
                        'method' => $method,
                        'path' => $specification->getPath(),
                    ];

                    if ($operationId === '') {
                        throw CannotGenerateCodeForOperation::becauseNoOperationIdSpecified($exceptionContext);
                    }

                    $responses = $this->getResponseDtoDefinitions($operation->responses, $specification, $exceptionContext);

                    $requestSchema = $this->findByMediaType($operation->requestBody, $specification->getMediaType());
                    $requestBody   = null;
                    if ($requestSchema !== null) {
                        $requestBody = new RequestBodyDtoDefinition(
                            $this->getPropertyGraph(
                                $requestSchema,
                                array_merge($exceptionContext, ['location' => 'request body'])
                            )
                        );
                    }

                    $parameters             = $this->mergeParameters($pathItem, $operation);
                    $requestDefinitions     = new RequestDtoDefinition(
                        $requestBody,
                        $this->parametersToDto('query', $parameters, array_merge($exceptionContext, ['location' => 'request query parameters'])),
                        $this->parametersToDto('path', $parameters, array_merge($exceptionContext, ['location' => 'request path parameters']))
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

        $serviceSubscriber = new ServiceSubscriberDefinition();

        return new GraphDefinition($specificationDefinitions, $serviceSubscriber);
    }

    /**
     * @param string[] $exceptionContext
     *
     * @return ResponseDtoDefinition[]
     */
    private function getResponseDtoDefinitions(
        ?Responses $responses,
        Specification $specification,
        array $exceptionContext
    ) : array {
        $responseDtoDefinitions = [];
        if ($responses instanceof Responses) {
            /**
             * @var string $responseCode
             */
            foreach ($responses->getResponses() as $responseCode => $response) {
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
                $responseDefinition       = new ResponseDtoDefinition($responseCode, $propertyDefinitions);
                $responseDtoDefinitions[] = $responseDefinition;
            }
        }

        return $responseDtoDefinitions;
    }

    /**
     * @param RequestBody|Response|Reference|null $body
     */
    private function findByMediaType($body, string $mediaType) : ?Schema
    {
        if ($body === null || $body instanceof Reference || $body->content === null) {
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
    private function parametersToDto(string $in, array $parameters, array $exceptionContext) : ?RequestParametersDtoDefinition
    {
        $properties = array_map(
            fn (Parameter $p) =>
                $this
                    ->getProperty($p->name, $p->schema, $exceptionContext, false)
                    ->setRequired($p->required)
                    ->setDescription($p->description),
            $this->filterSupportedParameters($in, $parameters)
        );

        if (count($properties) === 0) {
            return null;
        }

        return new RequestParametersDtoDefinition($properties);
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
            $type = new DtoDefinition($this->getPropertyGraph($property, $exceptionContext));
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

        if (! $isScalar && ! $allowNonScalar) {
            throw CannotGenerateCodeForOperation::becauseOnlyScalarAreAllowed($propertyName, $exceptionContext);
        }

        return $propertyDefinition;
    }
}
