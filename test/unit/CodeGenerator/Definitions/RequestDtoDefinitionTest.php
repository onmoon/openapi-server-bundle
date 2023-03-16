<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use function array_key_exists;

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
                'expected' => ['isEmpty' => true],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => false,
                    'hasQueryParameters' => false,
                    'hasPathParameters' => true,
                ],
                'expected' => ['isEmpty' => false],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => false,
                    'hasQueryParameters' => true,
                    'hasPathParameters' => false,
                ],
                'expected' => ['isEmpty' => false],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => true,
                    'hasQueryParameters' => false,
                    'hasPathParameters' => false,
                ],
                'expected' => ['isEmpty' => false],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => true,
                    'hasQueryParameters' => true,
                    'hasPathParameters' => false,
                ],
                'expected' => ['isEmpty' => false],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => true,
                    'hasQueryParameters' => false,
                    'hasPathParameters' => true,
                ],
                'expected' => ['isEmpty' => false],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => false,
                    'hasQueryParameters' => true,
                    'hasPathParameters' => true,
                ],
                'expected' => ['isEmpty' => false],
            ],
            [
                'conditions' => [
                    'hasBodyDtoDefinition' => true,
                    'hasQueryParameters' => true,
                    'hasPathParameters' => true,
                ],
                'expected' => ['isEmpty' => false],
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
        $propertyOne = new Property('body');
        $propertyOne->setRequired(true);

        $propertyTwo = new Property('queryParameters');
        $propertyTwo->setRequired(true);

        $propertyThree = new Property('pathParameters');
        $propertyThree->setRequired(true);

        $propertyObjectTypeDefinitionOne   = new DtoDefinition([]);
        $propertyObjectTypeDefinitionTwo   = new DtoDefinition([]);
        $propertyObjectTypeDefinitionThree = new DtoDefinition([]);

        $propertyDefinitionOne = new PropertyDefinition($propertyOne);
        $propertyDefinitionOne->setObjectTypeDefinition($propertyObjectTypeDefinitionOne);

        $propertyDefinitionTwo = new PropertyDefinition($propertyTwo);
        $propertyDefinitionTwo->setObjectTypeDefinition($propertyObjectTypeDefinitionTwo);

        $propertyDefinitionThree = new PropertyDefinition($propertyThree);
        $propertyDefinitionThree->setObjectTypeDefinition($propertyObjectTypeDefinitionThree);

        $payload = [];
        if ((bool) $conditions['hasBodyDtoDefinition']) {
            $payload['bodyDtoDefinition'] = $propertyDefinitionOne;
        }

        if ((bool) $conditions['hasQueryParameters']) {
            $payload['queryParameters'] = $propertyDefinitionTwo;
        }

        if ((bool) $conditions['hasPathParameters']) {
            $payload['pathParameters'] = $propertyDefinitionThree;
        }

        $requestDtoDefinition = new DtoDefinition($payload);

        Assert::assertSame($expected['isEmpty'], $requestDtoDefinition->isEmpty());

        if ($requestDtoDefinition->isEmpty() === true) {
            return;
        }

        $propertiesMap = [
            'body' => array_key_exists('bodyDtoDefinition', $payload) ? $payload['bodyDtoDefinition']->getObjectTypeDefinition() : null,
            'queryParameters' => array_key_exists('queryParameters', $payload) ? $payload['queryParameters']->getObjectTypeDefinition() : null,
            'pathParameters' => array_key_exists('pathParameters', $payload) ? $payload['pathParameters']->getObjectTypeDefinition() : null,
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
