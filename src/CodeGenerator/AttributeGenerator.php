<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoReference;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;

/** @psalm-suppress ClassMustBeFinal */
class AttributeGenerator
{
    public function setAllAttributes(GraphDefinition $graph): void
    {
        foreach ($graph->getSpecifications() as $specificationDefinition) {
            foreach ($specificationDefinition->getComponents() as $component) {
                $this->componentsPass($component->getDto());
            }

            foreach ($specificationDefinition->getOperations() as $operation) {
                $this->requestPass($operation->getRequest());

                foreach ($operation->getResponses() as $response) {
                    $this->responsePass($response->getResponseBody());
                }
            }
        }
    }

    public function componentsPass(?DtoReference $root): void
    {
        $this->treeWalk($root, static function (Property $specProperty, PropertyDefinition $property): void {
            $needValue = $specProperty->isRequired() && $specProperty->getDefaultValue() === null;
            $property
                ->setHasGetter(true)
                ->setHasSetter(! $needValue)
                ->setNullable(! $needValue || $specProperty->isNullable())
                ->setInConstructor($needValue);
        });
    }

    public function requestPass(?DtoReference $root): void
    {
        $this->treeWalk($root, static function (Property $specProperty, PropertyDefinition $property): void {
            $willExist = $specProperty->isRequired() || $specProperty->getDefaultValue() !== null;
            $property
                ->setHasGetter(true)
                ->setHasSetter(false)
                ->setNullable(! $willExist || $specProperty->isNullable())
                ->setInConstructor(false);
        });
    }

    public function responsePass(?DtoReference $root): void
    {
        $this->treeWalk($root, static function (Property $specProperty, PropertyDefinition $property): void {
            $needValue = $specProperty->isRequired() && $specProperty->getDefaultValue() === null;
            $property
                ->setHasGetter(true)
                ->setHasSetter(! $needValue)
                ->setNullable(! $needValue || $specProperty->isNullable())
                ->setInConstructor($needValue);
        });
    }

    /** @param callable(Property, PropertyDefinition): void $action */
    private function treeWalk(?DtoReference $root, callable $action): void
    {
        if (! $root instanceof DtoDefinition) {
            return;
        }

        foreach ($root->getProperties() as $property) {
            $specProperty = $property->getSpecProperty();
            $action($specProperty, $property);
            $this->treeWalk($property->getObjectTypeDefinition(), $action);
        }
    }
}
