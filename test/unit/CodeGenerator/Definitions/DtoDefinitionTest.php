<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/** @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition */
final class DtoDefinitionTest extends TestCase
{
    /** @return mixed[] */
    public static function dtoDefinitionProvider(): array
    {
        return [
            [
                'conditions' => [
                    'hasProperties' => false,
                    'hasImplements' => false,
                ],
                'expected' => ['isEmpty' => true],
            ],
            [
                'conditions' => [
                    'hasProperties' => true,
                    'hasImplements' => true,
                ],
                'expected' => ['isEmpty' => false],
            ],
        ];
    }

    /**
     * @param mixed[] $conditions
     * @param mixed[] $expected
     *
     * @dataProvider dtoDefinitionProvider
     */
    public function testDtoDefinition(array $conditions, array $expected): void
    {
        $propertyDefinition = new PropertyDefinition(
            new Property('SomeProperty')
        );
        $classDefinition    = new ClassDefinition();

        $payload               = [];
        $payload['properties'] = (bool) $conditions['hasProperties'] ? [$propertyDefinition] : [];
        $payload['implements'] = (bool) $conditions['hasImplements'] ? $classDefinition : null;

        $dtoDefinition = new DtoDefinition($payload['properties']);
        $dtoDefinition->setImplements($payload['implements']);

        Assert::assertSame($payload['properties'], $dtoDefinition->getProperties());
        Assert::assertSame($payload['implements'], $dtoDefinition->getImplements());
        Assert::assertSame($expected['isEmpty'], $dtoDefinition->isEmpty());
    }
}
