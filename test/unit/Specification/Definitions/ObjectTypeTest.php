<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Specification\Definitions;

use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType
 */
final class ObjectTypeTest extends TestCase
{
    public function testGetProperties(): void
    {
        $propertyData = [
            'name' => 'CustomName',
            'defaultValue' => 'Some Custom Value',
            'array' => true,
            'description' => 'Some Custom Description',
            'nullable' => true,
            'objectTypeDefinition' => new ObjectType([]),
            'pattern' => '/[0-9]+/',
            'required' => true,
            'scalarTypeId' => 777,
        ];

        $property = new Property($propertyData['name']);
        $property
            ->setDefaultValue($propertyData['defaultValue'])
            ->setArray($propertyData['array'])
            ->setDescription($propertyData['description'])
            ->setNullable($propertyData['nullable'])
            ->setObjectTypeDefinition($propertyData['objectTypeDefinition'])
            ->setPattern($propertyData['pattern'])
            ->setRequired($propertyData['required'])
            ->setScalarTypeId($propertyData['scalarTypeId']);

        $objectType = new ObjectType([$property]);

        Assert::assertSame([$property], $objectType->getProperties());
    }
}
