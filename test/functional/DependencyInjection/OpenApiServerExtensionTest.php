<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use OnMoon\OpenApiServerBundle\DependencyInjection\OpenApiServerExtension;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Throwable;

/**
 * @covers \OnMoon\OpenApiServerBundle\DependencyInjection\OpenApiServerExtension
 */
class OpenApiServerExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @return array<ExtensionInterface>
     */
    protected function getContainerExtensions(): array
    {
        return [
            new OpenApiServerExtension(),
        ];
    }

    /**
     * @return mixed[]
     *
     * @psalm-return list<list<string|bool>>
     */
    public function parameterLoadedDataProvider(): array
    {
        return [
            ['openapi.generated.code.root.path','someRootPath'],
            ['openapi.generated.code.root.namespace', 'someRootNameSpace'],
            ['openapi.generated.code.language.level', '1.2.3.4'],
            ['openapi.generated.code.dir.permissions', '0444'],
            ['openapi.generated.code.full.doc.blocks', true],
            ['openapi.send.nulls', true],
        ];
    }

    /**
     * @param string|bool $parameterValue
     *
     * @dataProvider parameterLoadedDataProvider
     */
    public function testLoadSetUpCorrectValues(string $parameterName, $parameterValue): void
    {
        $this->load([
            'root_path' => 'someRootPath',
            'root_name_space' => 'someRootNameSpace',
            'language_level' => '1.2.3.4',
            'generated_dir_permissions' => '0444',
            'full_doc_blocks' => true,
            'send_nulls' => true,
            'specs' => [['path' => 'test', 'name_space' => 'test', 'media_type' => 'application/json']],
        ]);

        $this->assertContainerBuilderHasParameter($parameterName, $parameterValue);
    }

    public function testLoadServiceDefinitionWithMethodCall(): void
    {
        $this->load([
            'root_path' => 'someRootPath',
            'root_name_space' => 'someRootNameSpace',
            'language_level' => '1.2.3.4',
            'generated_dir_permissions' => '0444',
            'full_doc_blocks' => true,
            'send_nulls' => true,
            'specs' => [['path' => 'test', 'name_space' => 'test', 'media_type' => 'application/json']],
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            SpecificationLoader::class,
            'registerSpec',
            [
                0,
                ['path' => 'test', 'name_space' => 'test', 'media_type' => 'application/json'],
            ]
        );
    }

    public function testLoadRootPathNotIssetThrowsError(): void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Please specify "root_path" parameter in package config if you are not ');

        $this->load(['root_name_space' => 'Hello\World']);
    }

    public function testLoadRootPathNotIssetSpecifyPath(): void
    {
        $this->load(['root_name_space' => 'App\World\Hello']);

        $this->assertContainerBuilderHasParameter(
            'openapi.generated.code.root.path',
            '%kernel.project_dir%/src/World/Hello'
        );
    }
}
