<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber;

use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\Definitions\ServiceSubscriberDefinition;

interface ServiceSubscriberFactory
{
    public function generateServiceSubscriber(ServiceSubscriberDefinition $definition) : GeneratedClass;
}
