<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceInterfaceDefinition;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
use function count;
use function Safe\substr;
use function strrpos;

class InterfaceGenerator
{
    private ClassDefinition $defaultDto;
    private ClassDefinition $defaultResponseDto;
    private ClassDefinition $defaultService;

    public function __construct()
    {
        $this->defaultDto         = $this->getDefaultInterface(Dto::class);
        $this->defaultResponseDto = $this->getDefaultInterface(ResponseDto::class);
        $this->defaultService     = $this->getDefaultInterface(RequestHandler::class);
    }

    public function setAllInterfaces(GraphDefinition $graph) : void
    {
        $graph->getServiceSubscriber()->setImplements([
            $this->getDefaultInterface(ApiLoader::class),
        ]);

        foreach ($graph->getSpecifications() as $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operation) {
                $makersInterface = null;
                /** @var ClassDefinition|null $responseClass */
                $responseClass = null;
                $responses     = $operation->getResponses();
                if (count($responses) > 1) {
                    $makersInterface = new GeneratedInterfaceDefinition();
                    $makersInterface->setExtends($this->defaultResponseDto);
                    $operation->setMarkersInterface($makersInterface);
                    $responseClass = $makersInterface;
                } else {
                    $makersInterface = $this->defaultResponseDto;
                    if (count($responses) === 1) {
                        $responseClass = $responses[0];
                    }
                }

                foreach ($responses as $response) {
                    $response->setImplements($makersInterface);
                    $this->setChildrenRecursive($response, $this->defaultDto);
                }

                $request = $operation->getRequest();
                if ($request !== null) {
                    $request->setImplements($this->defaultDto);
                    $this->setChildrenRecursive($request, $this->defaultDto);
                }

                $service = new ServiceInterfaceDefinition();
                $service
                    ->setResponseType($responseClass)
                    ->setRequestType($operation->getRequest())
                    ->setExtends($this->defaultService);
                $operation->setServiceInterface($service);
            }
        }
    }

    public function getDefaultInterface(string $className) : ClassDefinition
    {
        $lastPart = strrpos($className, '\\');
        if ($lastPart !== false) {
            $namespace = substr($className, 0, $lastPart);
            $name      = substr($className, $lastPart + 1);
        } else {
            $namespace = '';
            $name      = $className;
        }

        return (new ClassDefinition())
            ->setNamespace($namespace)
            ->setClassName($name);
    }

    public function setChildrenRecursive(DtoDefinition $root, ClassDefinition $implements) : void
    {
        foreach ($root->getProperties() as $property) {
            $objectDefinition = $property->getObjectTypeDefinition();
            if ($objectDefinition === null) {
                continue;
            }

            $objectDefinition->setImplements($implements);
            $this->setChildrenRecursive($objectDefinition, $implements);
        }
    }
}
