<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SpecificationDefinition;

class AttributeGenerator
{
    /**
     * @param SpecificationDefinition[] $specificationDefinitions
     */
    public function generate(array $specificationDefinitions) {
        foreach ($specificationDefinitions as $specificationDefinition) {
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

    private function requestPass(DtoDefinition $root) {
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

    private function responsePass(DtoDefinition $root) {
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
