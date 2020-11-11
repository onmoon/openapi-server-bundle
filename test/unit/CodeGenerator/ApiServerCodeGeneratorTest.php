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
use OnMoon\OpenApiServerBundle\CodeGenerator\InterfaceGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\NameGenerator;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ClassGraphReadyEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\FilesReadyEvent;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @covers  \OnMoon\OpenApiServerBundle\CodeGenerator\ApiServerCodeGenerator
 */
final class ApiServerCodeGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $graphDefinition = new GraphDefinition(
            [
                new SpecificationDefinition(
                    new SpecificationConfig('/', null, '/', 'application/json'),
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

        $interfaceGenerator = $this->createMock(InterfaceGenerator::class);
        $interfaceGenerator
            ->expects(self::once())
            ->method('setAllInterfaces')
            ->with($graphDefinition);

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

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [new ClassGraphReadyEvent($graphDefinition)],
                [new FilesReadyEvent([$generatedFileDefinition])]
            );

        $fileGenerator = $this->createMock(FileGenerator::class);
        $fileGenerator
            ->expects(self::once())
            ->method('generateAllFiles')
            ->with($graphDefinition)
            ->willReturn([$generatedFileDefinition]);

        $fileWriter = $this->createMock(FileWriter::class);
        $fileWriter
            ->expects(self::once())
            ->method('write')
            ->with(
                $generatedClassDefinition->getFilePath(),
                $generatedClassDefinition->getFileName(),
                $generatedFileDefinition->getFileContents()
            );

        $apiServerCodeGenerator = new ApiServerCodeGenerator(
            $graphGenerator,
            $nameGenerator,
            $interfaceGenerator,
            $fileGenerator,
            $attributeGenerator,
            $fileWriter,
            $eventDispatcher
        );
        $apiServerCodeGenerator->generate();
    }
}
