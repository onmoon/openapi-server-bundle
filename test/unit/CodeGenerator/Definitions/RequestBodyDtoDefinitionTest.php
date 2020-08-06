<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestBodyDtoDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestBodyDtoDefinition
 */
final class RequestBodyDtoDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function requestBodyDtoDefinitionProvider(): array
    {
        return [
            [
                'conditions' => ['hasProperties' => false],
            ],
            [
                'conditions' => ['hasProperties' => true],
            ],
        ];
    }

    /**
     * @param mixed[] $conditions
     *
     * @dataProvider requestBodyDtoDefinitionProvider
     */
    public function testRequestBodyDtoDefinition(array $conditions): void
    {
        /** @var PropertyDefinition|MockObject $propertyDefinitionMock */
        $propertyDefinitionMock = $this->createMock(PropertyDefinition::class);

        $payload               = [];
        $payload['properties'] = (bool) $conditions['hasProperties'] ? [$propertyDefinitionMock] : [];

        $dtoDefinition = new RequestBodyDtoDefinition($payload['properties']);

        Assert::assertSame($payload['properties'], $dtoDefinition->getProperties());
    }
}
