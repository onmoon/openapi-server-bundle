<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;

class AttributeGenerator
{
    public function setAllAttributes(GraphDefinition $graph) {
        foreach ($graph->getSpecifications() as $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operation) {
                if($operation->getRequest() !== null) {
                    $this->requestPass($operation->getRequest());
                }

                foreach ($operation->getResponses() as $response) {
                    $this->responsePass($response);
                }
            }
        }
    }

    public function requestPass(DtoDefinition $root) {
        foreach ($root->getProperties() as $property) {
            $property
                ->setHasGetter(true)
                ->setHasSetter(false)
                ->setNullable(!$property->isRequired() && $property->getDefaultValue() === null)
                ->setInConstructor(false);
            if($property->getObjectTypeDefinition() !== null) {
                $this->requestPass($property->getObjectTypeDefinition());
            }
        }
    }

    public function responsePass(DtoDefinition $root) {
        foreach ($root->getProperties() as $property) {
            $property
                ->setHasGetter(true)
                ->setHasSetter(!$property->isRequired())
                ->setNullable(!$property->isRequired() && $property->getDefaultValue() === null)
                ->setInConstructor($property->isRequired());
            if($property->getObjectTypeDefinition() !== null) {
                $this->responsePass($property->getObjectTypeDefinition());
            }
        }
    }
}
