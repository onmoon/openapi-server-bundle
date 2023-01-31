<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectSchema;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;

use function array_map;
use function count;

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

            $operationDefinitions = [];

            foreach ($parsedSpecification->getOperations() as $operationId => $operation) {
                $requestDefinition = $this->getRequestDefinition($operation);

                $singleHttpCode = null;
                $responses      = $this->getResponseDefinitions($operation->getResponses());
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

            $specificationDefinitions[] = new SpecificationDefinition($specificationConfig, $operationDefinitions);
        }

        $serviceSubscriber = new ServiceSubscriberDefinition();

        return new GraphDefinition($specificationDefinitions, $serviceSubscriber);
    }

    private function getRequestDefinition(Operation $operation): ?DtoDefinition
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
                ->setObjectTypeDefinition($this->objectTypeToDefinition($definition));
        }

        if (count($properties) === 0) {
            return null;
        }

        return new DtoDefinition($properties);
    }

    /**
     * @param array<string|int,ObjectSchema> $responses
     *
     * @return ResponseDefinition[]
     */
    private function getResponseDefinitions(array $responses): array
    {
        $responseDtoDefinitions = [];

        foreach ($responses as $statusCode => $response) {
            $responseDtoDefinitions[] = new ResponseDefinition(
                (string) $statusCode,
                $this->propertiesToDto($response->getProperties())
            );
        }

        return $responseDtoDefinitions;
    }

    private function objectTypeToDefinition(?ObjectSchema $type): ?DtoDefinition
    {
        if ($type === null) {
            return null;
        }

        return $this->propertiesToDto($type->getProperties());
    }

    /**
     * @param Property[] $properties
     */
    private function propertiesToDto(array $properties): DtoDefinition
    {
        return new DtoDefinition(array_map(
            fn (Property $p): PropertyDefinition => $this->propertyToDefinition($p),
            $properties
        ));
    }

    private function propertyToDefinition(Property $property): PropertyDefinition
    {
        return (new PropertyDefinition($property))
            ->setObjectTypeDefinition($this->objectTypeToDefinition($property->getObjectTypeDefinition()));
    }
}
