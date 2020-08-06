<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestParametersDtoDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestParametersDtoDefinition
 */
final class RequestParametersDtoDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function requestParametersDtoDefinitionProvider(): array
    {
        return [
            [
                'conditions' => [
                    'hasProperties' => false,
                ],
                'expected' => ['isEmpty' => true],
            ],
            [
                'conditions' => [
                    'hasProperties' => true,
                ],
                'expected' => ['isEmpty' => false],
            ],
        ];
    }

    /**
     * @param mixed[] $conditions
     * @param mixed[] $expected
     *
     * @dataProvider requestParametersDtoDefinitionProvider
     */
    public function testRequestParametersDtoDefinition(array $conditions, array $expected): void
    {
        /** @var PropertyDefinition|MockObject $propertyDefinitionMock */
        $propertyDefinitionMock = $this->createMock(PropertyDefinition::class);

        $payload = [];
        $payload['properties'] = (bool)$conditions['hasProperties'] ? [$propertyDefinitionMock] : [];

        $requestParametersDtoDefinition = new RequestParametersDtoDefinition($payload['properties']);

        Assert::assertSame($payload['properties'], $requestParametersDtoDefinition->getProperties());
        Assert::assertSame($expected['isEmpty'], $requestParametersDtoDefinition->isEmpty());
    }
}
