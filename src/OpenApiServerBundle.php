<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle;

use OnMoon\OpenApiServerBundle\DependencyInjection\CompilerPass;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class OpenApiServerBundle extends Bundle
{
    public const API_LOADER_TAG = 'openapi.api_loader';

    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(ApiLoader::class)->addTag(self::API_LOADER_TAG);
        $container->addCompilerPass(new CompilerPass(self::API_LOADER_TAG));
    }
}
