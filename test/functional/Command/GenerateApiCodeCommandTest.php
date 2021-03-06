<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Command\GenerateApiCodeCommand;
use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Tester\CommandTester;

use function rtrim;
use function Safe\sprintf;
use function ucfirst;

/**
 * @covers \OnMoon\OpenApiServerBundle\Command\GenerateApiCodeCommand
 */
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
        Assert::assertFileExists(TestKernel::$bundleRootPath . '/ServiceSubscriber/ApiServiceLoaderServiceSubscriber.php');
        Assert::assertFileExists(TestKernel::$bundleRootPath . '/Apis/' . $this->openapiNamespace . '/' . ucfirst($this->openapiOperationId) . '/' . ucfirst($this->openapiOperationId) . '.php');
        Assert::assertFileExists(TestKernel::$bundleRootPath . '/Apis/' . $this->openapiNamespace . '/' . ucfirst($this->openapiOperationId) . '/Dto/Request/' . ucfirst($this->openapiOperationId) . 'RequestDto.php');
        Assert::assertFileExists(TestKernel::$bundleRootPath . '/Apis/' . $this->openapiNamespace . '/' . ucfirst($this->openapiOperationId) . '/Dto/Request/PathParameters/PathParametersDto.php');
        Assert::assertFileExists(TestKernel::$bundleRootPath . '/Apis/' . $this->openapiNamespace . '/' . ucfirst($this->openapiOperationId) . '/Dto/Response/OK/' . ucfirst($this->openapiOperationId) . 'OKDto.php');
    }
}
