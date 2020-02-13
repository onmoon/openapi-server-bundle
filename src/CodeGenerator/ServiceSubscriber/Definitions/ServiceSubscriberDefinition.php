<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\BaseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\RequestHandlerInterfaceDefinition;

class ServiceSubscriberDefinition extends BaseDefinition
{
    /**
     * @var RequestHandlerInterfaceDefinition[]
     * @psalm-var list<RequestHandlerInterfaceDefinition>
     */
    private array $requestHandlerInterfaces;

    public function __construct(
        string $directoryPath,
        string $fileName,
        string $namespace,
        string $className,
        RequestHandlerInterfaceDefinition ...$requestHandlerInterfaces
    ) {
        parent::__construct($directoryPath, $fileName, $namespace, $className);

        $this->requestHandlerInterfaces = $requestHandlerInterfaces;
    }

    /**
     * @return RequestHandlerInterfaceDefinition[] $requestHandlerInterfaces
     *
     * @psalm-return list<RequestHandlerInterfaceDefinition> $requestHandlerInterfaces
     */
    public function requestHandlerInterfaces() : array
    {
        return $this->requestHandlerInterfaces;
    }

    public function setRequestHandlerInterfaces(RequestHandlerInterfaceDefinition ...$requestHandlerInterfaces) : void
    {
        $this->requestHandlerInterfaces = $requestHandlerInterfaces;
    }
}
