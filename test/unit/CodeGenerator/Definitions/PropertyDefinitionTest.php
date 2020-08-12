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
                    'classPropertyName' => 'CustomClassProperty',
                    'nullable' => false,
                    'getterName' => null,
                    'setterName' => null,
                    'hasGetter' => false,
                    'hasSetter' => false,
                    'inConstructor' => false,
                ],
                'conditions' => ['hasObjectTypeDefinition' => true],
                'expected' => [
                    'specProperty' => [
                        'name' => 'CustomProperty',
                        'isArray' => false,
                        'scalarTypeId' => null,
                    ],
                ],
            ],
            [
                'payload' => [
                    'classPropertyName' => 'CustomClassProperty',
                    'nullable' => true,
                    'getterName' => 'customGetter',
                    'setterName' => 'customSetter',
                    'hasGetter' => true,
                    'hasSetter' => true,
                    'inConstructor' => true,
                ],
                'conditions' => ['hasObjectTypeDefinition' => true],
                'expected' => [
                    'specProperty' => [
                        'name' => 'CustomProperty',
                        'isArray' => true,
                        'scalarTypeId' => 100,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $payload
     * @param mixed[] $conditions
     * @param mixed[] $expected
     *
     * @dataProvider propertyDefinitionProvider
     */
    public function testPropertyDefinition(array $payload, array $conditions, array $expected): void
    {
        $specProperty  = (new Property($expected['specProperty']['name']))
            ->setArray($expected['specProperty']['isArray'])
            ->setScalarTypeId($expected['specProperty']['scalarTypeId']);
        $dtoDefinition = new DtoDefinition([]);

        $payload['objectTypeDefinition'] = (bool) $conditions['hasObjectTypeDefinition'] ? $dtoDefinition : null;

        $propertyDefinition = new PropertyDefinition($specProperty);

        $propertyDefinition
            ->setClassPropertyName($payload['classPropertyName'])
            ->setObjectTypeDefinition($payload['objectTypeDefinition'])
            ->setNullable($payload['nullable'])
            ->setGetterName($payload['getterName'])
            ->setSetterName($payload['setterName'])
            ->setHasGetter($payload['hasGetter'])
            ->setHasSetter($payload['hasSetter'])
            ->setInConstructor($payload['inConstructor']);

        Assert::assertSame($specProperty, $propertyDefinition->getSpecProperty());
        Assert::assertSame($payload['classPropertyName'], $propertyDefinition->getClassPropertyName());
        Assert::assertSame($payload['objectTypeDefinition'], $propertyDefinition->getObjectTypeDefinition());
        Assert::assertSame($payload['nullable'], $propertyDefinition->isNullable());
        Assert::assertSame($payload['getterName'], $propertyDefinition->getGetterName());
        Assert::assertSame($payload['setterName'], $propertyDefinition->getSetterName());
        Assert::assertSame($payload['hasGetter'], $propertyDefinition->hasGetter());
        Assert::assertSame($payload['hasSetter'], $propertyDefinition->hasSetter());
        Assert::assertSame($payload['inConstructor'], $propertyDefinition->isInConstructor());
        Assert::assertSame($expected['specProperty']['name'], $propertyDefinition->getSpecPropertyName());
        Assert::assertSame($expected['specProperty']['isArray'], $propertyDefinition->isArray());
        Assert::assertSame($expected['specProperty']['scalarTypeId'], $propertyDefinition->getScalarTypeId());
    }
}
