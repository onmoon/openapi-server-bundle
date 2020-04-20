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
                if ($operation->getRequest() !== null) {
                    $result = array_merge($result, $this->generateDtoTree($operation->getRequest()));
                }

                foreach ($operation->getResponses() as $response) {
                    $result = array_merge($result, $this->generateDtoTree($response));
                }

                $markersInterface = $operation->getMarkersInterface();

                if ($markersInterface instanceof GeneratedInterfaceDefinition) {
                    $result[] = $this->interfaceGenerator->generate($markersInterface);
                }

                $servicesInterface = $operation->getServiceInterface();
                if ($servicesInterface === null) {
                    continue;
                }

                $result[] = $this->interfaceGenerator->generate($servicesInterface);
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
            if ($property->getObjectTypeDefinition() === null) {
                continue;
            }

            $result = array_merge($result, $this->generateDtoTree($property->getObjectTypeDefinition()));
        }

        return $result;
    }
}
