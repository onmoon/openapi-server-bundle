<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;

use function count;

class InterfaceGenerator
{
    private ClassDefinition $defaultDto;
    private ClassDefinition $defaultHandler;

    public function __construct()
    {
        $this->defaultDto         = ClassDefinition::fromFQCN(Dto::class);
        $this->defaultHandler     = ClassDefinition::fromFQCN(RequestHandler::class);
    }

    public function setAllInterfaces(GraphDefinition $graph): void
    {
        $graph->getServiceSubscriber()->setImplements([
            ClassDefinition::fromFQCN(ApiLoader::class),
        ]);

        foreach ($graph->getSpecifications() as $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operation) {
                $responses     = $operation->getResponses();
                foreach ($responses as $response) {
                    $this->setDtoInterfaceRecursive($response);
                }

                $request = $operation->getRequest();
                if ($request !== null) {
                    $this->setDtoInterfaceRecursive($request);
                }

                $service = new RequestHandlerInterfaceDefinition();
                $service
                    ->setResponseTypes($responses)
                    ->setRequestType($operation->getRequest())
                    ->setExtends($this->defaultHandler);
                $operation->setRequestHandlerInterface($service);
            }
        }
    }

    private function setDtoInterfaceRecursive(DtoDefinition $root): void
    {
        $root->setImplements($this->defaultDto);
        foreach ($root->getProperties() as $property) {
            $objectDefinition = $property->getObjectTypeDefinition();
            if ($objectDefinition === null) {
                continue;
            }
            $this->setDtoInterfaceRecursive($objectDefinition);
        }
    }
}
