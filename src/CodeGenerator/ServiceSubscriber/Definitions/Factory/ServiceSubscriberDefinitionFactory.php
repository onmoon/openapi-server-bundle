<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\Definitions\Factory;

use OnMoon\OpenApiServerBundle\CodeGenerator\Factory\BaseDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\Definitions\ServiceSubscriberDefinition;

class ServiceSubscriberDefinitionFactory extends BaseDefinitionFactory
{
    private const SERVICE_SUBSCRIBER_NAMESPACE = 'ServiceSubscriber';
    private const SERVICE_SUBSCRIBER_CLASSNAME = 'ApiServiceLoaderServiceSubscriber';

    public function create(
        RequestHandlerInterfaceDefinition ...$requestHandlerInterfaceDefinitions
    ) : ServiceSubscriberDefinition {
        $serviceSubscriberNamespace = $this->namingStrategy->buildNamespace(
            $this->rootNamespace(),
            self::SERVICE_SUBSCRIBER_NAMESPACE
        );
        $serviceSubscriberClassName = self::SERVICE_SUBSCRIBER_CLASSNAME;
        $serviceSubscriberPath      = $this->namingStrategy->buildPath(
            $this->rootPath(),
            self::SERVICE_SUBSCRIBER_NAMESPACE
        );
        $serviceSubscriberFileName  = self::SERVICE_SUBSCRIBER_CLASSNAME . '.php';

        return new ServiceSubscriberDefinition(
            $serviceSubscriberPath,
            $serviceSubscriberFileName,
            $serviceSubscriberNamespace,
            $serviceSubscriberClassName,
            ...$requestHandlerInterfaceDefinitions
        );
    }
}
