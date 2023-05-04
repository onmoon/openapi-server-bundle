<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDefinition
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
     * @dataProvider responseDtoDefinitionProvider
     */
    public function testResponseDtoDefinition(array $conditions): void
    {
        $propertyDefinition = new PropertyDefinition(
            new Property('SomeCustomName')
        );

        $payload               = [];
        $payload['statusCode'] = '200';
        $payload['properties'] = (bool) $conditions['hasProperties'] ? new DtoDefinition([$propertyDefinition]) : new DtoDefinition([]);

        $responseDtoDefinition = new ResponseDefinition(
            $payload['statusCode'],
            $payload['properties']
        );

        Assert::assertSame($payload['statusCode'], $responseDtoDefinition->getStatusCode());
        Assert::assertSame($payload['properties'], $responseDtoDefinition->getResponseBody());
    }
}
