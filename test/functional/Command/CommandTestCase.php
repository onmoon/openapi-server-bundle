<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

abstract class CommandTestCase extends KernelTestCase
{
    protected string $openapiNamespace   = 'PetStore';
    protected string $openapiOperationId = 'getGood';
    protected CommandTester $commandTester;
    protected Application $application;

    public function setUp(): void
    {
        $this->application = new Application(self::bootKernel());
    }

    public function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove([TestKernel::$bundleRootPath]);
        unset($this->commandTester, $this->application);

        parent::tearDown();
    }

    /**
     * {@inheritDoc}
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new class ('test', true) extends TestKernel {
            protected function configureContainer(ContainerConfigurator $c): void
            {
            }

            protected function configureRoutes(RoutingConfigurator $routes): void
            {
            }
        };
    }
}
