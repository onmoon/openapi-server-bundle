<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTestCase extends KernelTestCase
{
    /** @var string  */
    protected static $class = TestKernel::class;

    protected string $openapiNamespace   = 'PetStore';
    protected string $openapiOperationId = 'getGood';
    protected string $pathForFileGeneration;
    protected CommandTester $commandTester;
    protected Application $application;

    public function setUp(): void
    {
        $this->pathForFileGeneration = TestKernel::$bundleRootPath;
        $this->application           = new Application(static::bootKernel());
    }

    public function tearDown(): void
    {
        unset($this->commandTester, $this->application);
        parent::tearDown();
    }
}
