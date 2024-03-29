<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Interfaces;

use Symfony\Contracts\Service\ServiceSubscriberInterface;

interface ApiLoader extends ServiceSubscriberInterface
{
    public function get(string $interfaceName): ?RequestHandler;

    /** @return string[] */
    public function getAllowedCodes(string $apiClass, string $dtoClass): array;
}
