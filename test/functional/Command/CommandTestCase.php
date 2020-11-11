<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

abstract class CommandTestCase extends KernelTestCase
{
    /**
     * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string $class
     */
    protected static $class = CommandTestKernel::class;

    protected string $openapiNamespace   = 'PetStore';
    protected string $openapiOperationId = 'getGood';
    protected CommandTester $commandTester;
    protected Application $application;

    public function setUp(): void
    {
        $this->application = new Application(static::bootKernel());
    }

    public function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove([CommandTestKernel::$bundleRootPath]);
        unset($this->commandTester, $this->application);
        parent::tearDown();
    }
}
