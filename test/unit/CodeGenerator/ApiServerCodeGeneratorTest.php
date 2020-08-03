<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\ApiServerCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\AttributeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\FileGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FileWriter;
use OnMoon\OpenApiServerBundle\CodeGenerator\GraphGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\InterfaceGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\NameGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @covers  \OnMoon\OpenApiServerBundle\CodeGenerator\ApiServerCodeGenerator
 */
class ApiServerCodeGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $graphDefinition = $this->createStub(GraphDefinition::class);

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

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(self::exactly(2))
            ->method('dispatch');

        $generatedClassDefinitionStub = $this->createMock(GeneratedClassDefinition::class);
        $generatedClassDefinitionStub
            ->expects(self::once())
            ->method('getFilePath');
        $generatedClassDefinitionStub
            ->expects(self::once())
            ->method('getFileName');

        $generatedFileDefinition = new GeneratedFileDefinition($generatedClassDefinitionStub, 'test');
        $fileGenerator           = $this->createMock(FileGenerator::class);
        $fileGenerator
            ->expects(self::once())
            ->method('generateAllFiles')
            ->with($graphDefinition)
            ->willReturn([$generatedFileDefinition]);

        $fileWriter = $this->createMock(FileWriter::class);
        $fileWriter
            ->expects(self::once())
            ->method('write');

        $apiServerCodeGenerator = new ApiServerCodeGenerator($graphGenerator, $nameGenerator, $interfaceGenerator, $fileGenerator, $attributeGenerator, $fileWriter, $eventDispatcher);
        $apiServerCodeGenerator->generate();
    }
}
