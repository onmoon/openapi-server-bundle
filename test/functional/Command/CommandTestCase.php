<?php


namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;


use OnMoon\OpenApiServerBundle\Command\GenerateApiCodeCommand;
use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTestCase extends KernelTestCase
{
    protected string $pathForFileGeneration;
    protected CommandTester $commandTester;
    protected string $openapiNamespace = 'PetStore';
    protected string $openapiOperationId = 'getGood';

    public function setUp(): void
    {
        $this->pathForFileGeneration = __DIR__ . '/Generated';

        TestKernel::$rootPath = $this->pathForFileGeneration;
        TestKernel::$rootNamespace = __NAMESPACE__ . '\Generated';

        $application = new Application(static::bootKernel());
        $command = $application->find(GenerateApiCodeCommand::COMMAND);
        $this->commandTester = new CommandTester($command);
    }

    public function tearDown():void
    {
        unset($this->commandTester);
        parent::tearDown();
    }
}
