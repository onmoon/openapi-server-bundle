<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\GeneratedInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FileWriter;

class FileGenerator
{
    private DtoCodeGenerator $dtoGenerator;
    private InterfaceCodeGenerator $interfaceGenerator;
    private ServiceSubscriberCodeGenerator $serviceSubscriberGenerator;
    private FileWriter $writer;

    /**
     * FileGenerator constructor.
     * @param DtoCodeGenerator $dtoGenerator
     * @param InterfaceCodeGenerator $interfaceGenerator
     * @param ServiceSubscriberCodeGenerator $serviceSubscriberGenerator
     * @param FileWriter $writer
     */
    public function __construct(DtoCodeGenerator $dtoGenerator, InterfaceCodeGenerator $interfaceGenerator,
                                ServiceSubscriberCodeGenerator $serviceSubscriberGenerator, FileWriter $writer)
    {
        $this->dtoGenerator = $dtoGenerator;
        $this->interfaceGenerator = $interfaceGenerator;
        $this->serviceSubscriberGenerator = $serviceSubscriberGenerator;
        $this->writer = $writer;
    }


    /**
     * @return GeneratedClass[]
     */
    public function generate(GraphDefinition $graph) : array {
        /** @var GeneratedClass[] $result */
        $result = [];
        foreach ($graph->getSpecifications() as $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operation) {
                if($operation->getRequest() !== null) {
                    $result = array_merge($result, $this->generateDtoTree($operation->getRequest()));
                }

                foreach ($operation->getResponses() as $response) {
                    $result = array_merge($result, $this->generateDtoTree($response));
                }

                $markersInterface = $operation->getMarkersInterface();

                if($markersInterface instanceof GeneratedInterfaceDefinition) {
                    $result[] = $this->interfaceGenerator->generate($markersInterface);
                }

                $servicesInterface = $operation->getServiceInterface();
                if($servicesInterface !== null) {
                    $result[] = $this->interfaceGenerator->generate($servicesInterface);
                }
            }
        }

        $result[] = $this->serviceSubscriberGenerator->generate($graph);

        foreach ($result as $item) {
            $this->writer->write($item->getFileDirectoryPath(), $item->getFileName(), $item->getFileContents());
        }

        return $result;
    }

    /**
     * @return GeneratedClass[]
     */
    public function generateDtoTree(DtoDefinition $root) : array {
        $result = [];
        $result[] = $this->dtoGenerator->generate($root);
        foreach ($root->getProperties() as $property) {
            if($property->getObjectTypeDefinition() !== null) {
                $result = array_merge($result, $this->generateDtoTree($property->getObjectTypeDefinition()));
            }
        }

        return $result;
    }
}
