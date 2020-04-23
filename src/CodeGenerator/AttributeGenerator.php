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
                    $this->setTreeAttributes($request);
                }

                foreach ($operation->getResponses() as $response) {
                    $this->setTreeAttributes($response);
                }
            }
        }
    }

    public function setTreeAttributes(DtoDefinition $root) : void
    {
        foreach ($root->getProperties() as $property) {
            $property
                ->setHasGetter(true)
                ->setHasSetter(! $property->isRequired())
                ->setNullable(! $property->isRequired() && $property->getDefaultValue() === null)
                ->setInConstructor($property->isRequired());

            $object = $property->getObjectTypeDefinition();
            if ($object === null) {
                continue;
            }

            $this->setTreeAttributes($object);
        }
    }
}
