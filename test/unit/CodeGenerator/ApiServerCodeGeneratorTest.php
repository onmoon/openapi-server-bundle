<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\ApiServerCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\AttributeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\FileGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FileWriter;
use OnMoon\OpenApiServerBundle\CodeGenerator\GraphGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\NameGenerator;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ClassGraphReadyEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\FilesReadyEvent;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Webmozart\Assert\Assert;

use const DIRECTORY_SEPARATOR;

/** @covers  \OnMoon\OpenApiServerBundle\CodeGenerator\ApiServerCodeGenerator */
final class ApiServerCodeGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $graphDefinition = new GraphDefinition(
            [
                new SpecificationDefinition(
                    new SpecificationConfig('/', null, '/', 'application/json'),
                    [],
                    []
                ),
                new SpecificationDefinition(
                    new SpecificationConfig('/someAnotherPath', null, '/SomeNameSpace', 'application/json'),
                    [],
                    []
                ),
            ],
            new ServiceSubscriberDefinition()
        );

        $graphGenerator = $this->createMock(GraphGenerator::class);
        $graphGenerator
            ->expects(self::once())
            ->method('generateClassGraph')
            ->willReturn($graphDefinition);

        $attributeGenerator = $this->createMock(AttributeGenerator::class);
        $attributeGenerator
            ->expects(self::once())
            ->method('setAllAttributes')
            ->with($graphDefinition);

        $nameGenerator = $this->createMock(NameGenerator::class);
        $nameGenerator
            ->expects(self::once())
            ->method('setAllNamesAndPaths')
            ->with($graphDefinition);

        $generatedClassDefinition = new GeneratedClassDefinition();
        $generatedClassDefinition->setFilePath('test_file_path');
        $generatedClassDefinition->setFileName('test_file_name');

        $generatedFileDefinition = new GeneratedFileDefinition($generatedClassDefinition, 'test');

        $generatedClassDefinitionTwo = new GeneratedClassDefinition();
        $generatedClassDefinitionTwo->setFilePath('test_file_path_two');
        $generatedClassDefinitionTwo->setFileName('test_file_name_two');

        $generatedFileDefinitionTwo = new GeneratedFileDefinition($generatedClassDefinitionTwo, 'test_two');

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $matcher         = self::exactly(2);
        $eventDispatcher
            ->expects($matcher)
            ->method('dispatch')
            ->willReturnCallback(function (Event $event) use ($matcher, $graphDefinition, $generatedFileDefinition, $generatedFileDefinitionTwo): object {
                match ($matcher->numberOfInvocations()) {
                    1 => $this->assertEquals($event, new ClassGraphReadyEvent($graphDefinition)),
                    2 => $this->assertEquals($event, new FilesReadyEvent([$generatedFileDefinition, $generatedFileDefinitionTwo])),
                };

                return $event;
            });

        $fileGenerator = $this->createMock(FileGenerator::class);
        $fileGenerator
            ->expects(self::once())
            ->method('generateAllFiles')
            ->with($graphDefinition)
            ->willReturn([$generatedFileDefinition, $generatedFileDefinitionTwo]);

        $fileWriter = $this->createMock(FileWriter::class);
        $matcher    = self::exactly(2);
        $fileWriter
            ->expects($matcher)
            ->method('write')
            ->willReturnCallback(function (string $path, string $filename, string $contents) use ($matcher, $generatedClassDefinition, $generatedFileDefinition, $generatedClassDefinitionTwo, $generatedFileDefinitionTwo): void {
                match ($matcher->numberOfInvocations()) {
                    1 => $this->assertEquals([$path, $filename, $contents], [$generatedClassDefinition->getFilePath(), $generatedClassDefinition->getFileName(), $generatedFileDefinition->getFileContents()]),
                    2 => $this->assertEquals([$path, $filename, $contents], [$generatedClassDefinitionTwo->getFilePath(), $generatedClassDefinitionTwo->getFileName(), $generatedFileDefinitionTwo->getFileContents()]),
                };
            });

        $apiServerCodeGenerator = new ApiServerCodeGenerator(
            $graphGenerator,
            $nameGenerator,
            $fileGenerator,
            $attributeGenerator,
            $fileWriter,
            $eventDispatcher
        );

        $writtenFiles = $apiServerCodeGenerator->generate();

        Assert::count($writtenFiles, 2);
        Assert::same($writtenFiles[0], 'test_file_path' . DIRECTORY_SEPARATOR . 'test_file_name');
        Assert::same($writtenFiles[1], 'test_file_path_two' . DIRECTORY_SEPARATOR . 'test_file_name_two');
    }
}
