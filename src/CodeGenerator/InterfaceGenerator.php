<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\GeneratedInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\InterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ServiceInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;

class InterfaceGenerator
{
    private InterfaceDefinition $defaultDto;
    private InterfaceDefinition $defaultResponseDto;
    private InterfaceDefinition $defaultService;

    public function __construct() {
        $this->defaultDto = $this->getDefaultInterface(Dto::class);
        $this->defaultResponseDto = $this->getDefaultInterface(ResponseDto::class);
        $this->defaultService = $this->getDefaultInterface(RequestHandler::class);
    }

    public function generate(GraphDefinition $graph) {
        foreach ($graph->getSpecifications() as $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operation) {
                $makersInterface = null;
                /** @var ClassDefinition|null $responseClass */
                $responseClass = null;
                $responses = $operation->getResponses();
                if (count($responses) > 1) {
                    $makersInterface = new GeneratedInterfaceDefinition();
                    $makersInterface->setExtends($this->defaultResponseDto);
                    $operation->setMarkersInterface($makersInterface);
                    $responseClass = $makersInterface;
                } else {
                    $makersInterface = $this->defaultResponseDto;
                    if(count($responses) === 1) {
                        $responseClass = $responses[0];
                    }
                }

                foreach ($responses as $response) {
                    $response->setImplements($makersInterface);
                    $this->setChildrenRecursive($response, $this->defaultDto);
                }

                if($operation->getRequest() !== null) {
                    $operation->getRequest()->setImplements($this->defaultDto);
                    $this->setChildrenRecursive($operation->getRequest(), $this->defaultDto);
                }

                $service = new ServiceInterfaceDefinition();
                $service
                    ->setExtends($this->defaultService)
                    ->setResponseType($responseClass)
                    ->setRequestType($operation->getRequest());
                $operation->setServiceInterface($service);
            }
        }
    }

    public function getDefaultInterface(string $className) {
        $lastPart = strrpos($className, '\\');
        $namespace = substr($className, 0, $lastPart);
        $name = substr($className, $lastPart + 1);
        return (new InterfaceDefinition())
            ->setNamespace($namespace)
            ->setClassName($name);
    }

    public function setChildrenRecursive(DtoDefinition $root, InterfaceDefinition $implements) {
        foreach ($root->getProperties() as $property) {
            $objectDefinition = $property->getObjectTypeDefinition();
            if ($objectDefinition !== null) {
                $objectDefinition->setImplements($implements);
                $this->setChildrenRecursive($objectDefinition, $implements);
            }
        }
    }

}