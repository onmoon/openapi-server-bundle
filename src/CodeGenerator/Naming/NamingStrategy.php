<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Naming;

interface NamingStrategy
{
    /**
     * @psalm-return class-string<\OnMoon\OpenApiServerBundle\Interfaces\Service>
     */
    public function getInterfaceFQCN(string $apiNameSpace, string $operationId) : string;

    public function stringToNamespace(string $text) : string;

    public function stringToMethodName(string $text) : string;

    public function buildNamespace(string ...$parts) : string;

    public function buildPath(string ...$parts) : string;

    public function isAllowedPhpPropertyName(string $name) : bool;
}
