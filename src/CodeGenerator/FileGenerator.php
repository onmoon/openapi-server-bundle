<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoReference;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\DtoCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\InterfaceCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\ServiceSubscriberCodeGenerator;

use function array_push;

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

    /** @return GeneratedFileDefinition[] */
    public function generateAllFiles(GraphDefinition $graph): array
    {
        /** @var GeneratedFileDefinition[] $result */
        $result = [];
        foreach ($graph->getSpecifications() as $specificationDefinition) {
            foreach ($specificationDefinition->getComponents() as $component) {
                array_push($result, ...$this->generateDtoTree($component->getDto()));
            }

            foreach ($specificationDefinition->getOperations() as $operation) {
                array_push($result, ...$this->generateDtoTree($operation->getRequest()));

                foreach ($operation->getResponses() as $response) {
                    array_push($result, ...$this->generateDtoTree($response->getResponseBody()));
                }

                $result[] = $this->interfaceGenerator->generate($operation->getRequestHandlerInterface());
            }
        }

        $result[] = $this->serviceSubscriberGenerator->generate($graph);

        return $result;
    }

    /** @return GeneratedFileDefinition[] */
    public function generateDtoTree(?DtoReference $root): array
    {
        if (! $root instanceof DtoDefinition) {
            return [];
        }

        $result   = [];
        $result[] = $this->dtoGenerator->generate($root);
        foreach ($root->getProperties() as $property) {
            array_push($result, ...$this->generateDtoTree($property->getObjectTypeDefinition()));
        }

        return $result;
    }
}
