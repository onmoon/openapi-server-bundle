<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;


class GeneratedInterfaceDefinition extends InterfaceDefinition
{
    private ?string $fileName = null;
    private ?InterfaceDefinition $extends = null;

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

    /**
     * @return InterfaceDefinition|null
     */
    public function getExtends(): ?InterfaceDefinition
    {
        return $this->extends;
    }

    /**
     * @param InterfaceDefinition|null $extends
     * @return GeneratedInterfaceDefinition
     */
    public function setExtends(?InterfaceDefinition $extends): GeneratedInterfaceDefinition
    {
        $this->extends = $extends;
        return $this;
    }

}