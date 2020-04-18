<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;


class GeneratedInterfaceDefinition extends InterfaceDefinition
{
    private ?string $fileName = null;

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     * @return GeneratedInterfaceDefinition
     */
    public function setFileName(?string $fileName): GeneratedInterfaceDefinition
    {
        $this->fileName = $fileName;
        return $this;
    }
}