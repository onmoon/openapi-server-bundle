<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;


class GraphDefinition
{
    /** @var SpecificationDefinition[] */
    private array $specifications;
    private ServiceSubscriberDefinition $serviceSubscriber;

    /**
     * GraphDefinition constructor.
     * @param array|SpecificationDefinition[] $specifications
     * @param ServiceSubscriberDefinition $serviceSubscriber
     */
    public function __construct($specifications, ServiceSubscriberDefinition $serviceSubscriber)
    {
        $this->specifications = $specifications;
        $this->serviceSubscriber = $serviceSubscriber;
    }

    /**
     * @return SpecificationDefinition[]
     */
    public function getSpecifications(): array
    {
        return $this->specifications;
    }

    /**
     * @return ServiceSubscriberDefinition
     */
    public function getServiceSubscriber(): ServiceSubscriberDefinition
    {
        return $this->serviceSubscriber;
    }

}
