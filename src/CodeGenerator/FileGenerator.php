<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SpecificationDefinition;

class FileGenerator
{
    private DtoGenerator $dtoGenerator;

    /**
     * FilesGenerator constructor.
     * @param DtoGenerator $dtoGenerator
     */
    public function __construct(DtoGenerator $dtoGenerator)
    {
        $this->dtoGenerator = $dtoGenerator;
    }

    /**
     * @param SpecificationDefinition[] $specificationDefinitions
     * @return GeneratedClass[]
     */
    public function generate(array $specificationDefinitions) : array {
        $result = [];
        foreach ($specificationDefinitions as $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operation) {
                if($operation->getRequest() !== null) {
                    $result = array_merge($result, $this->generateDtoTree($operation->getRequest()));
                }

                foreach ($operation->getResponses() as $response) {
                    $result = array_merge($result, $this->generateDtoTree($response));
                }
            }
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
                $result[] = $this->dtoGenerator->generate($property->getObjectTypeDefinition());
            }
        }

        return $result;
    }
}
