<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition
 */
final class SpecificationDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function specificationDefinitionProvider(): array
    {
        return [
            [
                'conditions' => [
                    'hasOperations' => false,
                ],
            ],
            [
                'conditions' => [
                    'hasOperations' => true,
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $conditions
     *
     * @dataProvider specificationDefinitionProvider
     */
    public function testSpecificationDefinition(array $conditions): void
    {
        /** @var SpecificationConfig|MockObject $specificationConfigMock */
        $specificationConfigMock = $this->createMock(SpecificationConfig::class);
        /** @var OperationDefinition|MockObject $operationDefinitionMock */
        $operationDefinitionMock = $this->createMock(OperationDefinition::class);

        $payload = [];
        $payload['specification'] = $specificationConfigMock;
        $payload['operations'] = (bool) $conditions['hasOperations'] ? [$operationDefinitionMock] : [];

        $specificationDefinition = new SpecificationDefinition(
            $payload['specification'],
            $payload['operations']
        );

        Assert::assertSame($payload['operations'], $specificationDefinition->getOperations());
        Assert::assertSame($payload['specification'], $specificationDefinition->getSpecification());
    }
}
