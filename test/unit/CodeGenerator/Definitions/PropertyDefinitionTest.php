<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition
 */
final class PropertyDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function propertyDefinitionProvider(): array
    {
        return [
            [
                'payload' => [
                    'property' => [
                        'classPropertyName' => 'CustomClassProperty',
                        'nullable' => false,
                        'getterName' => null,
                        'setterName' => null,
                        'hasGetter' => false,
                        'hasSetter' => false,
                        'inConstructor' => false,
                    ],
                    'specProperty' => [
                        'name' => 'CustomProperty',
                        'description' => null,
                        'isArray' => false,
                        'scalarTypeId' => null,
                    ],
                    'hasObjectTypeDefinition' => false,
                ],
            ],
            [
                'payload' => [
                    'property' => [
                        'classPropertyName' => 'CustomClassProperty',
                        'nullable' => true,
                        'getterName' => 'customGetter',
                        'setterName' => 'customSetter',
                        'hasGetter' => true,
                        'hasSetter' => true,
                        'inConstructor' => true,
                    ],
                    'specProperty' => [
                        'name' => 'CustomProperty',
                        'description' => 'CustomDescription',
                        'isArray' => true,
                        'scalarTypeId' => 100,
                    ],
                    'hasObjectTypeDefinition' => true,
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $payload
     *
     * @dataProvider propertyDefinitionProvider
     */
    public function testPropertyDefinition(array $payload): void
    {
        $specProperty = (new Property($payload['specProperty']['name']))
            ->setDescription($payload['specProperty']['description'])
            ->setArray($payload['specProperty']['isArray'])
            ->setScalarTypeId($payload['specProperty']['scalarTypeId']);

        $objectTypeDefinition = (bool) $payload['hasObjectTypeDefinition'] ? new DtoDefinition([]) : null;

        $propertyDefinition = (new PropertyDefinition($specProperty))
            ->setClassPropertyName($payload['property']['classPropertyName'])
            ->setObjectTypeDefinition($objectTypeDefinition)
            ->setNullable($payload['property']['nullable'])
            ->setGetterName($payload['property']['getterName'])
            ->setSetterName($payload['property']['setterName'])
            ->setHasGetter($payload['property']['hasGetter'])
            ->setHasSetter($payload['property']['hasSetter'])
            ->setInConstructor($payload['property']['inConstructor']);

        Assert::assertSame($payload['property']['classPropertyName'], $propertyDefinition->getClassPropertyName());
        Assert::assertSame($objectTypeDefinition, $propertyDefinition->getObjectTypeDefinition());
        Assert::assertSame($payload['property']['nullable'], $propertyDefinition->isNullable());
        Assert::assertSame($payload['property']['getterName'], $propertyDefinition->getGetterName());
        Assert::assertSame($payload['property']['setterName'], $propertyDefinition->getSetterName());
        Assert::assertSame($payload['property']['hasGetter'], $propertyDefinition->hasGetter());
        Assert::assertSame($payload['property']['hasSetter'], $propertyDefinition->hasSetter());
        Assert::assertSame($payload['property']['inConstructor'], $propertyDefinition->isInConstructor());

        Assert::assertSame($specProperty, $propertyDefinition->getSpecProperty());
        Assert::assertSame($payload['specProperty']['name'], $propertyDefinition->getSpecPropertyName());
        Assert::assertSame($payload['specProperty']['isArray'], $propertyDefinition->isArray());
        Assert::assertSame($payload['specProperty']['scalarTypeId'], $propertyDefinition->getScalarTypeId());
        Assert::assertSame($payload['specProperty']['description'], $propertyDefinition->getDescription());
    }
}
