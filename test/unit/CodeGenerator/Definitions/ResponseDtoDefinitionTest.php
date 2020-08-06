<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestParametersDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition
 */
final class ResponseDtoDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function responseDtoDefinitionProvider(): array
    {
        return [
            [
                'conditions' => [
                    'hasProperties' => false,
                ],
                'expected' => [
                    'isEmpty' => true,
                ],
            ],
            [
                'conditions' => [
                    'hasProperties' => true,
                ],
                'expected' => [
                    'isEmpty' => false,
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $conditions
     * @param mixed[] $expected
     *
     * @dataProvider responseDtoDefinitionProvider
     */
    public function testResponseDtoDefinition(array $conditions, array $expected): void
    {
        /** @var PropertyDefinition|MockObject $propertyDefinitionMock */
        $propertyDefinitionMock = $this->createMock(PropertyDefinition::class);

        $payload = [];
        $payload['statusCode'] = '200';
        $payload['properties'] = (bool) $conditions['hasProperties'] ? [$propertyDefinitionMock] : [];

        $responseDtoDefinition = new ResponseDtoDefinition(
            $payload['statusCode'],
            $payload['properties']
        );

        Assert::assertSame($payload['statusCode'], $responseDtoDefinition->getStatusCode());
        Assert::assertSame($payload['properties'], $responseDtoDefinition->getProperties());
        Assert::assertSame($expected['isEmpty'], $responseDtoDefinition->isEmpty());
    }
}
