<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestParametersDtoDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition
 */
final class RequestDtoDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function requestDtoDefinitionProvider(): array
    {
        return [
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => false,
                    'hasQueryParameters' => false,
                    'hasPathParameters' => false,
                ],
                'expected' => [
                    'isEmpty' => true,
                ],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => false,
                    'hasQueryParameters' => false,
                    'hasPathParameters' => true,
                ],
                'expected' => [
                    'isEmpty' => false,
                ],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => false,
                    'hasQueryParameters' => true,
                    'hasPathParameters' => false,
                ],
                'expected' => [
                    'isEmpty' => false,
                ],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => true,
                    'hasQueryParameters' => false,
                    'hasPathParameters' => false,
                ],
                'expected' => [
                    'isEmpty' => false,
                ],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => true,
                    'hasQueryParameters' => true,
                    'hasPathParameters' => false,
                ],
                'expected' => [
                    'isEmpty' => false,
                ],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => true,
                    'hasQueryParameters' => false,
                    'hasPathParameters' => true,
                ],
                'expected' => [
                    'isEmpty' => false,
                ],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => false,
                    'hasQueryParameters' => true,
                    'hasPathParameters' => true,
                ],
                'expected' => [
                    'isEmpty' => false,
                ],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => true,
                    'hasQueryParameters' => true,
                    'hasPathParameters' => true,
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
     * @dataProvider requestDtoDefinitionProvider
     */
    public function testRequestDtoDefinition(array $conditions, array $expected): void
    {
        /** @var RequestBodyDtoDefinition|MockObject $bodyDtoDefinitionMock */
        $bodyDtoDefinitionMock = $this->createMock(RequestBodyDtoDefinition::class);
        /** @var RequestParametersDtoDefinition|MockObject $queryParametersMock */
        $queryParametersMock = $this->createMock(RequestParametersDtoDefinition::class);
        /** @var RequestParametersDtoDefinition|MockObject $pathParametersMock */
        $pathParametersMock = $this->createMock(RequestParametersDtoDefinition::class);

        $payload = [];
        $payload['bodyDtoDefinition'] = (bool) $conditions['hasBodyDtoDefinition'] ? $bodyDtoDefinitionMock : null;
        $payload['queryParameters'] = (bool) $conditions['hasQueryParameters'] ? $queryParametersMock : null;
        $payload['pathParameters'] = (bool) $conditions['hasPathParameters'] ? $pathParametersMock : null;

        $requestDtoDefinition = new RequestDtoDefinition(
            $payload['bodyDtoDefinition'],
            $payload['queryParameters'],
            $payload['pathParameters']
        );

        Assert::assertSame($expected['isEmpty'], $requestDtoDefinition->isEmpty());

        if ($requestDtoDefinition->isEmpty() === true) {
            return;
        }

        $propertiesMap = [
            'body' => $payload['bodyDtoDefinition'],
            'queryParameters' => $payload['queryParameters'],
            'pathParameters' => $payload['pathParameters'],
        ];

        foreach ($requestDtoDefinition->getProperties() as $property) {
            Assert::assertSame(
                $propertiesMap[$property->getSpecProperty()->getName()],
                $property->getObjectTypeDefinition()
            );
            Assert::assertTrue($property->getSpecProperty()->isRequired());
        }
    }
}
