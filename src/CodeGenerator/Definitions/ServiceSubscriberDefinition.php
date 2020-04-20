<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;


class ServiceSubscriberDefinition extends GeneratedClassDefinition
{
    /** @var ClassDefinition[] */
    private array $implements = [];

    /**
     * @return ClassDefinition[]
     */
    public function getImplements(): array
    {
        return $this->implements;
    }

    /**
     * @param ClassDefinition[] $implements
     * @return ServiceSubscriberDefinition
     */
    public function setImplements(array $implements): ServiceSubscriberDefinition
    {
        $this->implements = $implements;
        return $this;
    }

}
