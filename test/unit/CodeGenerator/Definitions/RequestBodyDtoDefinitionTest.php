<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use PHPUnit\Framework\Assert;
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
        $propertyDefinition = new PropertyDefinition(
            new Property('SomeCustomName')
        );

        $payload               = [];
        $payload['properties'] = (bool) $conditions['hasProperties'] ? [$propertyDefinition] : [];

        $dtoDefinition = new RequestBodyDtoDefinition($payload['properties']);

        Assert::assertSame($payload['properties'], $dtoDefinition->getProperties());
    }
}
