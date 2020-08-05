<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition
 */
final class GraphDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function graphDefinitionProvider(): array
    {
        return [
            [
                'conditions' => ['hasSpecifications' => false],
            ],
            [
                'conditions' => ['hasSpecifications' => true],
            ],
        ];
    }

    /**
     * @param mixed[] $conditions
     *
     * @dataProvider graphDefinitionProvider
     */
    public function testGraphDefinition(array $conditions): void
    {
        /** @var SpecificationDefinition|MockObject $specificationDefinitionMock */
        $specificationDefinitionMock = $this->createMock(SpecificationDefinition::class);
        /** @var ServiceSubscriberDefinition|MockObject $serviceSubscriberDefinitionMock */
        $serviceSubscriberDefinitionMock = $this->createMock(ServiceSubscriberDefinition::class);

        $payload                      = [];
        $payload['specifications']    = (bool) $conditions['hasSpecifications'] ? [$specificationDefinitionMock] : [];
        $payload['serviceSubscriber'] = $serviceSubscriberDefinitionMock;

        $generatedInterfaceDefinition = new GraphDefinition(
            $payload['specifications'],
            $payload['serviceSubscriber']
        );

        Assert::assertSame($payload['specifications'], $generatedInterfaceDefinition->getSpecifications());
        Assert::assertSame($payload['serviceSubscriber'], $generatedInterfaceDefinition->getServiceSubscriber());
    }
}
