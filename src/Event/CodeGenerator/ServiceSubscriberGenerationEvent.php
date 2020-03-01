<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\Definitions\ServiceSubscriberDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The ServiceSubscriberGenerationEvent event occurs before the service
 * subscriber is generated.
 *
 * This event allows you to modify the definition of the generated
 * service subscriber customizing the generated code.
 */
class ServiceSubscriberGenerationEvent extends Event
{
    private ServiceSubscriberDefinition $definition;

    public function __construct(ServiceSubscriberDefinition $definition)
    {
        $this->definition = $definition;
    }

    public function definition() : ServiceSubscriberDefinition
    {
        return $this->definition;
    }
}
