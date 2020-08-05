<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition
 */
final class DtoDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function dtoDefinitionProvider(): array
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
        /** @var PropertyDefinition|MockObject $propertyDefinitionMock */
        $propertyDefinitionMock = $this->createMock(PropertyDefinition::class);
        /** @var ClassDefinition|MockObject $classDefinitionMock */
        $classDefinitionMock = $this->createMock(ClassDefinition::class);

        $payload               = [];
        $payload['properties'] = (bool) $conditions['hasProperties'] ? [$propertyDefinitionMock] : [];
        $payload['implements'] = (bool) $conditions['hasImplements'] ? $classDefinitionMock : null;

        $dtoDefinition = new DtoDefinition($payload['properties']);
        $dtoDefinition->setImplements($payload['implements']);

        Assert::assertSame($payload['properties'], $dtoDefinition->getProperties());
        Assert::assertSame($payload['implements'], $dtoDefinition->getImplements());
        Assert::assertSame($expected['isEmpty'], $dtoDefinition->isEmpty());
    }
}
