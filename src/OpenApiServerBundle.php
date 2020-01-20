<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle;

use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\DependencyInjection\CompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenApiServerBundle extends Bundle
{
    const API_LOADER_TAG = 'openapi.api_loader';

    public function build(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(ApiLoader::class)->addTag(self::API_LOADER_TAG);
        $container->addCompilerPass(new CompilerPass(self::API_LOADER_TAG));

        parent::build($container);
    }
}
