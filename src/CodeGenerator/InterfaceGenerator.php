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
    private ClassDefinition $defaultResponseDto;
    private ClassDefinition $defaultHandler;

    public function __construct()
    {
        $this->defaultDto         = ClassDefinition::fromFQCN(Dto::class);
        $this->defaultResponseDto = ClassDefinition::fromFQCN(ResponseDto::class);
        $this->defaultHandler     = ClassDefinition::fromFQCN(RequestHandler::class);
    }

    public function setAllInterfaces(GraphDefinition $graph): void
    {
        $graph->getServiceSubscriber()->setImplements([
            ClassDefinition::fromFQCN(ApiLoader::class),
        ]);

        foreach ($graph->getSpecifications() as $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operation) {
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

                $service = new RequestHandlerInterfaceDefinition();
                $service
                    ->setResponseType($responseClass)
                    ->setRequestType($operation->getRequest())
                    ->setExtends($this->defaultHandler);
                $operation->setRequestHandlerInterface($service);
            }
        }
    }

    private function setChildrenRecursive(DtoDefinition $root, ClassDefinition $implements): void
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
