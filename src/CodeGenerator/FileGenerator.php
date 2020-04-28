<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\DtoCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\InterfaceCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\ServiceSubscriberCodeGenerator;
use function array_merge;

class FileGenerator
{
    private DtoCodeGenerator $dtoGenerator;
    private InterfaceCodeGenerator $interfaceGenerator;
    private ServiceSubscriberCodeGenerator $serviceSubscriberGenerator;

    public function __construct(
        DtoCodeGenerator $dtoGenerator,
        InterfaceCodeGenerator $interfaceGenerator,
        ServiceSubscriberCodeGenerator $serviceSubscriberGenerator
    ) {
        $this->dtoGenerator               = $dtoGenerator;
        $this->interfaceGenerator         = $interfaceGenerator;
        $this->serviceSubscriberGenerator = $serviceSubscriberGenerator;
    }

    /**
     * @return GeneratedFileDefinition[]
     */
    public function generateAllFiles(GraphDefinition $graph) : array
    {
        /** @var GeneratedFileDefinition[] $result */
        $result = [];
        foreach ($graph->getSpecifications() as $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operation) {
                $request = $operation->getRequest();
                if ($request !== null) {
                    $result = array_merge($result, $this->generateDtoTree($request));
                }

                foreach ($operation->getResponses() as $response) {
                    $result = array_merge($result, $this->generateDtoTree($response));
                }

                $markersInterface = $operation->getMarkersInterface();
                if ($markersInterface instanceof GeneratedInterfaceDefinition) {
                    $result[] = $this->interfaceGenerator->generate($markersInterface);
                }

                $result[] = $this->interfaceGenerator->generate($operation->getRequestHandlerInterface());
            }
        }

        $result[] = $this->serviceSubscriberGenerator->generate($graph);

        return $result;
    }

    /**
     * @return GeneratedFileDefinition[]
     */
    public function generateDtoTree(DtoDefinition $root) : array
    {
        $result   = [];
        $result[] = $this->dtoGenerator->generate($root);
        foreach ($root->getProperties() as $property) {
            $object = $property->getObjectTypeDefinition();
            if ($object === null) {
                continue;
            }

            $result = array_merge($result, $this->generateDtoTree($object));
        }

        return $result;
    }
}
