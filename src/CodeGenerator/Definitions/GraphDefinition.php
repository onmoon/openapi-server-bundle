<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

final class GraphDefinition
{
    /** @var SpecificationDefinition[] */
    private array $specifications;
    private ServiceSubscriberDefinition $serviceSubscriber;

    /** @param SpecificationDefinition[] $specifications */
    public function __construct(array $specifications, ServiceSubscriberDefinition $serviceSubscriber)
    {
        $this->specifications    = $specifications;
        $this->serviceSubscriber = $serviceSubscriber;
    }

    /** @return SpecificationDefinition[] */
    public function getSpecifications(): array
    {
        return $this->specifications;
    }

    public function getServiceSubscriber(): ServiceSubscriberDefinition
    {
        return $this->serviceSubscriber;
    }
}
