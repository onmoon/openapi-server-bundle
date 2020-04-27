<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

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
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use function array_key_exists;
use function array_map;

class GraphGenerator
{
    private SpecificationLoader $loader;

    public function __construct(SpecificationLoader $loader)
    {
        $this->loader = $loader;
    }

    public function generateClassGraph() : GraphDefinition
    {
        $specificationDefinitions = [];
        foreach ($this->loader->list() as $specificationName => $specificationConfig) {
            $parsedSpecification = $this->loader->load($specificationName);

            $operationDefinitions = [];

            foreach ($parsedSpecification->getOperations() as $operationId => $operation) {
                $requestBody = null;
                $bodyType    = $operation->getRequestBody();
                if ($bodyType !== null) {
                    $requestBody = new RequestBodyDtoDefinition(
                        $this->propertiesToDefinitions($bodyType->getProperties())
                    );
                }

                $requestDefinitions = new RequestDtoDefinition(
                    $requestBody,
                    $this->parametersToDto('query', $operation->getRequestParameters()),
                    $this->parametersToDto('path', $operation->getRequestParameters())
                );

                $operationDefinitions[] = new OperationDefinition(
                    $operation->getUrl(),
                    $operation->getMethod(),
                    $operationId,
                    $operation->getRequestHandlerName(),
                    $operation->getSummary(),
                    $requestDefinitions->isEmpty() ? null : $requestDefinitions,
                    $this->getResponseDtoDefinitions($operation->getResponses())
                );
            }

            $specificationDefinitions[] = new SpecificationDefinition($specificationConfig, $operationDefinitions);
        }

        $serviceSubscriber = new ServiceSubscriberDefinition();

        return new GraphDefinition($specificationDefinitions, $serviceSubscriber);
    }

    /**
     * @param ObjectType[] $responses
     *
     * @return ResponseDtoDefinition[]
     */
    private function getResponseDtoDefinitions(array $responses) : array
    {
        $responseDtoDefinitions = [];

        foreach ($responses as $statusCode => $response) {
            $responseDtoDefinitions[] = new ResponseDtoDefinition(
                $statusCode,
                $this->propertiesToDefinitions($response->getProperties())
            );
        }

        return $responseDtoDefinitions;
    }

    /**
     * @param ObjectType[] $parameters
     */
    private function parametersToDto(string $in, array $parameters) : ?RequestParametersDtoDefinition
    {
        if (! array_key_exists($in, $parameters)) {
            return null;
        }

        return new RequestParametersDtoDefinition(
            $this->propertiesToDefinitions($parameters[$in]->getProperties())
        );
    }

    private function objectTypeToDefinition(?ObjectType $type) : ?DtoDefinition
    {
        if ($type === null) {
            return null;
        }

        return new DtoDefinition($this->propertiesToDefinitions($type->getProperties()));
    }

    /**
     * @param Property[] $properties
     *
     * @return PropertyDefinition[]
     */
    private function propertiesToDefinitions(array $properties) : array
    {
        return array_map(
            fn (Property $p) : PropertyDefinition => $this->propertyToDefinition($p),
            $properties
        );
    }

    private function propertyToDefinition(Property $property) : PropertyDefinition
    {
        return (new PropertyDefinition($property->getName()))
            ->setRequired($property->isRequired())
            ->setArray($property->isArray())
            ->setScalarTypeId($property->getScalarTypeId())
            ->setObjectTypeDefinition($this->objectTypeToDefinition($property->getObjectTypeDefinition()))
            ->setDescription($property->getDescription())
            ->setDefaultValue($property->getDefaultValue());
    }
}
