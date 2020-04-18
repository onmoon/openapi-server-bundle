<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\GeneratedInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\InterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;

class InterfaceGenerator
{
    private InterfaceDefinition $defaultDto;
    private InterfaceDefinition $defaultResponseDto;

    public function __construct() {
        $this->defaultDto = $this->getDefaultInterface(Dto::class);
        $this->defaultResponseDto = $this->getDefaultInterface(ResponseDto::class);
    }

    /**
     * @param SpecificationDefinition[] $specificationDefinitions
     */
    public function generate(array $specificationDefinitions) {
        foreach ($specificationDefinitions as $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operation) {
                $makersInterface = null;
                if (count($operation->getResponses()) > 1) {
                    $makersInterface = new GeneratedInterfaceDefinition();
                    $operation->setMarkersInterface($makersInterface);
                } else {
                    $makersInterface = $this->getDefaultInterface(ResponseDto::class);
                }

                foreach ($operation->getResponses() as $response) {
                    $response->setImplements($makersInterface);
                    $this->setChildrenRecursive($response, $this->defaultDto);
                }

                if($operation->getRequest() !== null) {
                    $operation->getRequest()->setImplements($this->defaultDto);
                    $this->setChildrenRecursive($operation->getRequest(), $this->defaultDto);
                }
            }
        }
    }

    private function getDefaultInterface(string $className) {
        $lastPart = strrpos($className, '\\');
        $namespace = substr($className, 0, $lastPart);
        $name = substr($className, $lastPart + 1);
        return (new InterfaceDefinition())
            ->setNamespace($namespace)
            ->setClassName($name);
    }

    private function setChildrenRecursive(DtoDefinition $root, InterfaceDefinition $implements) {
        foreach ($root->getProperties() as $property) {
            $objectDefinition = $property->getObjectTypeDefinition();
            if ($objectDefinition !== null) {
                $objectDefinition->setImplements($implements);
                $this->setChildrenRecursive($objectDefinition, $implements);
            }
        }
    }

}