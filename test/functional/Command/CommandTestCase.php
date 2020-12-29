<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollectionBuilder;

use function get_class;

abstract class CommandTestCase extends KernelTestCase
{
    protected string $openapiNamespace   = 'PetStore';
    protected string $openapiOperationId = 'getGood';
    protected CommandTester $commandTester;
    protected Application $application;

    /**
     * @param mixed[] $data
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        if (Kernel::MINOR_VERSION < 2) {
            $kernelClass = new class ('test', true) extends TestKernel {
                protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
                {
                }

                protected function configureRoutes(RouteCollectionBuilder $routes): void
                {
                }
            };
        } else {
            $kernelClass = new class ('test', true) extends TestKernel {
                protected function configureContainer(ContainerConfigurator $c): void
                {
                }

                protected function configureRoutes(RoutingConfigurator $routes): void
                {
                }
            };
        }

        self::$class = get_class($kernelClass);
    }

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
}
