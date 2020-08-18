<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use OnMoon\OpenApiServerBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

use function Safe\sprintf;

/**
 * @covers \OnMoon\OpenApiServerBundle\DependencyInjection\Configuration
 */
class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    /**
     * @return list<list<array<string, list<array<string, string>>>|string>>
     */
    public function parametersIsRequiredDataProvider(): array
    {
        return [
            [['specs' => [[]]], 'path'],
            [['specs' => [['path' => 'test']]], 'name_space'],
            [['specs' => [['path' => 'test', 'name_space' => 'test']]], 'media_type'],
        ];
    }

    /**
     * @param list<list<array<string, list<array<string, string>>>|string>> $configuration
     *
     * @dataProvider parametersIsRequiredDataProvider
     */
    public function testParametersIsRequired(array $configuration, string $parameterName): void
    {
        $this->assertConfigurationIsInvalid(
            [$configuration],
            sprintf('The child node "%s" at path "open_api_server.specs.0" must be configured.', $parameterName)
        );
    }

    /**
     * @return list<list<array<string, list<array<string, string>>|string>|string>>
     */
    public function parametersCannotBeEmptyDataProvider(): array
    {
        return [
            [['root_name_space' => ''], 'root_name_space'],
            [['language_level' => ''], 'language_level'],
            [['generated_dir_permissions' => ''], 'generated_dir_permissions'],
            [['specs' => [['path' => '']]], 'specs.0.path'],
            [['specs' => [['path' => 'test', 'name_space' => '']]], 'specs.0.name_space'],
            [['specs' => [['path' => 'test', 'name_space' => 'test', 'media_type' => '']]], 'specs.0.media_type'],
        ];
    }

    /**
     * @param list<list<array<string, list<array<string, string>>>|string>> $configuration
     *
     * @dataProvider parametersCannotBeEmptyDataProvider
     */
    public function testParametersCannotBeEmpty(array $configuration, string $parameterName): void
    {
        $this->assertConfigurationIsInvalid([$configuration], sprintf('The path "open_api_server.%s" cannot contain an empty value, but got "".', $parameterName));
    }

    /**
     * @return list<list<array<string, list<array<string, string>>>|string>>
     */
    public function parametersEnumDataProvider(): array
    {
        return [
            [['specs' => [['path' => 'test', 'type' => 'someRandomString']]], 'specs.0.type', 'someRandomString', '"yaml", "json"'],
            [
                ['specs' => [['path' => 'test', 'name_space' => 'test', 'media_type' => 'someRandomString']]],
                'specs.0.media_type',
                'someRandomString',
                '"application\/json"',
            ],
        ];
    }

    /**
     * @param list<list<array<string, list<array<string, string>>>|string>> $configuration, string $parameterName
     *
     * @dataProvider parametersEnumDataProvider
     */
    public function testParametersEnumDataProvider(
        array $configuration,
        string $parameterName,
        string $notAllowedValue,
        string $permissibleValues
    ): void {
        $this->assertConfigurationIsInvalid([$configuration], sprintf(
            'The value "%s" is not allowed for path "open_api_server.%s". Permissible values: %s',
            $notAllowedValue,
            $parameterName,
            $permissibleValues
        ));
    }

    public function testParametersDefaultValues(): void
    {
        $this->assertProcessedConfigurationEquals([], [
            'root_name_space' => 'App\Generated',
            'language_level' => '7.4.0',
            'generated_dir_permissions' => '0755',
            'full_doc_blocks' => false,
            'send_nulls' => false,
            'specs' => [],
        ]);
    }
}
