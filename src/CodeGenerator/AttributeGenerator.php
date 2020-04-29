<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;

class AttributeGenerator
{
    public function setAllAttributes(GraphDefinition $graph) : void
    {
        foreach ($graph->getSpecifications() as $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operation) {
                $request = $operation->getRequest();
                if ($request !== null) {
                    $this->requestPass($request);
                }

                foreach ($operation->getResponses() as $response) {
                    $this->responsePass($response);
                }
            }
        }
    }

    public function requestPass(DtoDefinition $root) : void
    {
        foreach ($root->getProperties() as $property) {
            $specProperty = $property->getSpecProperty();
            $property
                ->setHasGetter(true)
                ->setHasSetter(false)
                ->setNullable(! $specProperty->isRequired() && $specProperty->getDefaultValue() === null)
                ->setInConstructor(false);

            $object = $property->getObjectTypeDefinition();
            if ($object === null) {
                continue;
            }

            $this->requestPass($object);
        }
    }

    public function responsePass(DtoDefinition $root) : void
    {
        foreach ($root->getProperties() as $property) {
            $specProperty = $property->getSpecProperty();
            $property
                ->setHasGetter(true)
                ->setHasSetter(! $specProperty->isRequired())
                ->setNullable(! $specProperty->isRequired() || $specProperty->getDefaultValue() !== null)
                ->setInConstructor($specProperty->isRequired());

            $object = $property->getObjectTypeDefinition();
            if ($object === null) {
                continue;
            }

            $this->responsePass($object);
        }
    }
}
