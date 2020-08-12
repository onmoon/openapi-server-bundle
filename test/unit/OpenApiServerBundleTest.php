<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit;

use OnMoon\OpenApiServerBundle\DependencyInjection\CompilerPass;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\OpenApiServerBundle;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpenApiServerBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $container = new ContainerBuilder();
        $bundle    = new OpenApiServerBundle();
        $bundle->build($container);

        $config                  = $container->getCompilerPassConfig();
        $passes                  = $config->getBeforeOptimizationPasses();
        $autoconfiguredInstances = $container->getAutoconfiguredInstanceof();

        $compilerPass = false;

        foreach ($passes as $pass) {
            if (! ($pass instanceof CompilerPass)) {
                continue;
            }

            $compilerPass = true;
        }

        Assert::assertTrue($compilerPass, 'CompilerPass was not found');

        $autoconfiguredApiLoader = isset($autoconfiguredInstances[ApiLoader::class]);

        Assert::assertTrue($autoconfiguredApiLoader, 'ApiLoader was not autoconfigured');
    }
}
