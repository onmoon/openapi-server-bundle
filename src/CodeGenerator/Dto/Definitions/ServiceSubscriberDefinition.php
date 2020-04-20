<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;


class ServiceSubscriberDefinition extends ClassDefinition
{
    private ?string $fileName = null;
    private ?string $filePath = null;
    /** @var InterfaceDefinition[] */
    private array $implements = [];

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     * @return ServiceSubscriberDefinition
     */
    public function setFileName(?string $fileName): ServiceSubscriberDefinition
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    /**
     * @param string|null $filePath
     * @return ServiceSubscriberDefinition
     */
    public function setFilePath(?string $filePath): ServiceSubscriberDefinition
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return InterfaceDefinition[]
     */
    public function getImplements(): array
    {
        return $this->implements;
    }

    /**
     * @param InterfaceDefinition[] $implements
     * @return ServiceSubscriberDefinition
     */
    public function setImplements(array $implements): ServiceSubscriberDefinition
    {
        $this->implements = $implements;
        return $this;
    }

}
