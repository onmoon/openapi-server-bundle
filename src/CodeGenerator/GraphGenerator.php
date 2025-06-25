<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ComponentDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ComponentReference;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoReference;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Exception\CannotParseOpenApi;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectReference;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectSchema;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;

use function array_map;
use function count;

/** @psalm-suppress ClassMustBeFinal */
class GraphGenerator
{
    private SpecificationLoader $loader;

    public function __construct(SpecificationLoader $loader)
    {
        $this->loader = $loader;
    }

    public function generateClassGraph(): GraphDefinition
    {
        $specificationDefinitions = [];
        foreach ($this->loader->list() as $specificationName => $specificationConfig) {
            $parsedSpecification = $this->loader->load($specificationName);

            $componentDefinitions = [];
            $componentSchemas     = $parsedSpecification->getComponentSchemas();
            foreach ($componentSchemas as $name => $_objectSchema) {
                $componentDefinitions[] = new ComponentDefinition($name);
            }

            foreach ($componentDefinitions as $component) {
                $component->setDto($this->objectSchemaToDefinition($componentSchemas[$component->getName()], $componentDefinitions));
            }

            $operationDefinitions = [];

            foreach ($parsedSpecification->getOperations() as $operationId => $operation) {
                $requestDefinition = $this->getRequestDefinition($operation, $componentDefinitions);

                $singleHttpCode = null;
                $responses      = $this->getResponseDefinitions($operation->getResponses(), $componentDefinitions);
                if (count($responses) === 1 && $responses[0]->getResponseBody()->isEmpty()) {
                    $singleHttpCode = $responses[0]->getStatusCode();
                    $responses      = [];
                }

                $service = new RequestHandlerInterfaceDefinition(
                    $requestDefinition,
                    array_map(
                        static fn (ResponseDefinition $response) => $response->getResponseBody(),
                        $responses
                    )
                );

                $operationDefinitions[] = new OperationDefinition(
                    $operation->getUrl(),
                    $operation->getMethod(),
                    $operationId,
                    $operation->getRequestHandlerName(),
                    $operation->getSummary(),
                    $singleHttpCode,
                    $requestDefinition,
                    $responses,
                    $service
                );
            }

            $specificationDefinitions[] = new SpecificationDefinition($specificationConfig, $operationDefinitions, $componentDefinitions);
        }

        $serviceSubscriber = new ServiceSubscriberDefinition();

        return new GraphDefinition($specificationDefinitions, $serviceSubscriber);
    }

    /** @param ComponentDefinition[] $components */
    private function getRequestDefinition(Operation $operation, array $components): ?DtoDefinition
    {
        $fields = [
            'pathParameters' => $operation->getRequestParameters()['path'] ?? null,
            'queryParameters' => $operation->getRequestParameters()['query'] ?? null,
            'body' => $operation->getRequestBody(),
        ];

        $properties = [];

        foreach ($fields as $name => $definition) {
            if ($definition === null) {
                continue;
            }

            $specProperty = (new Property($name))->setRequired(true);
            $properties[] = (new PropertyDefinition($specProperty))
                ->setObjectTypeDefinition($this->objectTypeToDefinition($definition, $components));
        }

        if (count($properties) === 0) {
            return null;
        }

        return new DtoDefinition($properties);
    }

    /**
     * @param array<string|int,ObjectSchema|ObjectReference> $responses
     * @param ComponentDefinition[]                          $components
     *
     * @return ResponseDefinition[]
     */
    private function getResponseDefinitions(array $responses, array $components): array
    {
        $responseDtoDefinitions = [];

        foreach ($responses as $statusCode => $response) {
            $responseDtoDefinitions[] = new ResponseDefinition(
                (string) $statusCode,
                $this->objectTypeToDefinition($response, $components)
            );
        }

        return $responseDtoDefinitions;
    }

    /** @param ComponentDefinition[] $components */
    private function objectTypeToDefinition(ObjectSchema|ObjectReference $type, array $components): DtoReference
    {
        if ($type instanceof ObjectReference) {
            foreach ($components as $component) {
                if ($component->getName() === $type->getSchemaName()) {
                    return new ComponentReference($component);
                }
            }

            throw CannotParseOpenApi::becauseUnknownReferenceFound($type->getSchemaName());
        }

        return $this->objectSchemaToDefinition($type, $components);
    }

    /** @param ComponentDefinition[] $components */
    private function objectSchemaToDefinition(ObjectSchema $type, array $components): DtoDefinition
    {
        return new DtoDefinition(array_map(
            fn (Property $p): PropertyDefinition => $this->propertyToDefinition($p, $components),
            $type->getProperties()
        ));
    }

    /** @param ComponentDefinition[] $components */
    private function propertyToDefinition(Property $property, array $components): PropertyDefinition
    {
        $definition = new PropertyDefinition($property);
        $objectType = $property->getObjectTypeDefinition();
        if ($objectType !== null) {
            $definition->setObjectTypeDefinition($this->objectTypeToDefinition($objectType, $components));
        }

        return $definition;
    }
}
