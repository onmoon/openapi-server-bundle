<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Command\GenerateApiCodeCommand;
use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Tester\CommandTester;

use function rtrim;
use function ucfirst;

use const DIRECTORY_SEPARATOR;

/** @covers \OnMoon\OpenApiServerBundle\Command\GenerateApiCodeCommand */
class GenerateApiCodeCommandTest extends CommandTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $command             = $this->application->find(GenerateApiCodeCommand::COMMAND);
        $this->commandTester = new CommandTester($command);
    }

    public function testGeneration(): void
    {
        $this->commandTester->execute([
            'command'  => GenerateApiCodeCommand::COMMAND,
        ]);

        $output = $this->commandTester->getDisplay();

        Assert::assertEquals(sprintf('API server code generated in: %s', TestKernel::$bundleRootPath), rtrim($output));
        Assert::assertSame(0, $this->commandTester->getStatusCode());
        Assert::assertDirectoryExists(TestKernel::$bundleRootPath);
        Assert::assertDirectoryIsReadable(TestKernel::$bundleRootPath);
        Assert::assertFileExists(TestKernel::$bundleRootPath . DIRECTORY_SEPARATOR . 'ServiceSubscriber' . DIRECTORY_SEPARATOR . 'ApiServiceLoaderServiceSubscriber.php');
        Assert::assertFileExists(TestKernel::$bundleRootPath . DIRECTORY_SEPARATOR . 'Apis' . DIRECTORY_SEPARATOR . $this->openapiNamespace . DIRECTORY_SEPARATOR . ucfirst($this->openapiOperationId) . DIRECTORY_SEPARATOR . ucfirst($this->openapiOperationId) . '.php');
        Assert::assertFileExists(TestKernel::$bundleRootPath . DIRECTORY_SEPARATOR . 'Apis' . DIRECTORY_SEPARATOR . $this->openapiNamespace . DIRECTORY_SEPARATOR . ucfirst($this->openapiOperationId) . DIRECTORY_SEPARATOR . 'Dto' . DIRECTORY_SEPARATOR . 'Request' . DIRECTORY_SEPARATOR . ucfirst($this->openapiOperationId) . 'RequestDto.php');
        Assert::assertFileExists(TestKernel::$bundleRootPath . DIRECTORY_SEPARATOR . 'Apis' . DIRECTORY_SEPARATOR . $this->openapiNamespace . DIRECTORY_SEPARATOR . ucfirst($this->openapiOperationId) . DIRECTORY_SEPARATOR . 'Dto' . DIRECTORY_SEPARATOR . 'Request' . DIRECTORY_SEPARATOR . 'PathParameters' . DIRECTORY_SEPARATOR . 'PathParametersDto.php');
        Assert::assertFileExists(TestKernel::$bundleRootPath . DIRECTORY_SEPARATOR . 'Components' . DIRECTORY_SEPARATOR . $this->openapiNamespace . DIRECTORY_SEPARATOR . 'GoodResponseSchema' . DIRECTORY_SEPARATOR . 'GoodResponseSchema.php');
    }
}
