<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

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

        $kernelClass = new class ('test', true) extends TestKernel {
            protected function configureContainer(ContainerConfigurator $c): void
            {
            }

            protected function configureRoutes(RoutingConfigurator $routes): void
            {
            }
        };

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

    protected static function getKernelClass(): string
    {
        return self::class;
    }
}
