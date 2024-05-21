<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Naming;

// phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;

interface NamingStrategy
{
    /** @psalm-return class-string<RequestHandler> */
    public function getInterfaceFQCN(string $apiNameSpace, string $operationId): string;

    public function stringToNamespace(string $text): string;

    public function stringToMethodName(string $text): string;

    public function buildNamespace(string ...$parts): string;

    public function buildPath(string ...$parts): string;

    public function isAllowedPhpPropertyName(string $name): bool;
}
