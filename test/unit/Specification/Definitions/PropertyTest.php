<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Specification\Definitions;

use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use function array_key_exists;

/**
 * @covers \OnMoon\OpenApiServerBundle\Specification\Definitions\Property
 */
final class PropertyTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function propertiesProvider(): array
    {
        return [
            [
                'propertyData' => ['name' => 'CustomName'],
            ],
            [
                'propertyData' => [
                    'name' => 'CustomName',
                    'defaultValue' => true,
                ],
            ],
            [
                'propertyData' => [
                    'name' => 'CustomName',
                    'defaultValue' => 1.01,
                ],
            ],
            [
                'propertyData' => [
                    'name' => 'CustomName',
                    'defaultValue' => 1,
                ],
            ],
            [
                'propertyData' => [
                    'name' => 'CustomName',
                    'defaultValue' => null,
                    'array' => false,
                    'description' => null,
                    'nullable' => false,
                    'objectTypeDefinition' => null,
                    'pattern' => null,
                    'required' => false,
                    'scalarTypeId' => null,
                ],
            ],
            [
                'propertyData' => [
                    'name' => 'CustomName',
                    'defaultValue' => 'Some Custom Value',
                    'array' => true,
                    'description' => 'Some Custom Description',
                    'nullable' => true,
                    'objectTypeDefinition' => new ObjectType([]),
                    'pattern' => '/[0-9]+/',
                    'required' => true,
                    'scalarTypeId' => 777,
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $propertyData
     *
     * @dataProvider propertiesProvider
     */
    public function testProperties(array $propertyData): void
    {
        $property = new Property($propertyData['name']);

        if (array_key_exists('defaultValue', $propertyData)) {
            $property->setDefaultValue($propertyData['defaultValue']);
        }

        if (array_key_exists('array', $propertyData)) {
            $property->setArray($propertyData['array']);
        }

        if (array_key_exists('description', $propertyData)) {
            $property->setDescription($propertyData['description']);
        }

        if (array_key_exists('nullable', $propertyData)) {
            $property->setNullable($propertyData['nullable']);
        }

        if (array_key_exists('objectTypeDefinition', $propertyData)) {
            $property->setObjectTypeDefinition($propertyData['objectTypeDefinition']);
        }

        if (array_key_exists('pattern', $propertyData)) {
            $property->setPattern($propertyData['pattern']);
        }

        if (array_key_exists('required', $propertyData)) {
            $property->setRequired($propertyData['required']);
        }

        if (array_key_exists('scalarTypeId', $propertyData)) {
            $property->setScalarTypeId($propertyData['scalarTypeId']);
        }

        Assert::assertSame($property->getName(), $propertyData['name']);
        Assert::assertSame($property->getDefaultValue(), $propertyData['defaultValue'] ?? null);
        Assert::assertSame($property->isArray(), $propertyData['array'] ?? false);
        Assert::assertSame($property->getDescription(), $propertyData['description'] ?? null);
        Assert::assertSame($property->isNullable(), $propertyData['nullable'] ?? false);
        Assert::assertSame($property->getObjectTypeDefinition(), $propertyData['objectTypeDefinition'] ?? null);
        Assert::assertSame($property->getPattern(), $propertyData['pattern'] ?? null);
        Assert::assertSame($property->isRequired(), $propertyData['required'] ?? false);
        Assert::assertSame($property->getScalarTypeId(), $propertyData['scalarTypeId'] ?? null);
    }
}
