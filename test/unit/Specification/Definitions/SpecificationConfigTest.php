<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Specification\Definitions;

use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/** @covers \OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig */
final class SpecificationConfigTest extends TestCase
{
    /** @return mixed[] */
    public static function specificationConfigsProvider(): array
    {
        return [
            [
                'specificationConfigData' => [
                    'path' => '/some/custom/path',
                    'type' => null,
                    'namespace' => 'Custom\\Namespace',
                    'mediaType' => 'application/json',
                ],
            ],
            [
                'specificationConfigData' => [
                    'path' => '/some/custom/path',
                    'type' => 'custom-type',
                    'namespace' => 'Custom\\Namespace',
                    'mediaType' => 'application/json',
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $specificationConfigData
     *
     * @dataProvider specificationConfigsProvider
     */
    public function testSpecificationConfigs(array $specificationConfigData): void
    {
        $specification = new SpecificationConfig(
            $specificationConfigData['path'],
            $specificationConfigData['type'],
            $specificationConfigData['namespace'],
            $specificationConfigData['mediaType'],
        );

        Assert::assertSame($specification->getPath(), $specificationConfigData['path']);
        Assert::assertSame($specification->getType(), $specificationConfigData['type']);
        Assert::assertSame($specification->getNameSpace(), $specificationConfigData['namespace']);
        Assert::assertSame($specification->getMediaType(), $specificationConfigData['mediaType']);
    }
}
