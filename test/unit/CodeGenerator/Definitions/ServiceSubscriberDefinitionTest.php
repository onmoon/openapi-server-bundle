<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition
 */
final class ServiceSubscriberDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function serviceSubscriberDefinitionProvider(): array
    {
        return [
            [
                'conditions' => [
                    'hasImplements' => false,
                ],
            ],
            [
                'conditions' => [
                    'hasImplements' => true,
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $conditions
     *
     * @dataProvider serviceSubscriberDefinitionProvider
     */
    public function testServiceSubscriberDefinition(array $conditions): void
    {
        /** @var ClassDefinition|MockObject $classDefinitionMock */
        $classDefinitionMock = $this->createMock(ClassDefinition::class);

        $payload = [];
        $payload['implements'] = (bool) $conditions['hasImplements'] ? [$classDefinitionMock] : [];

        $serviceSubscriberDefinition = new ServiceSubscriberDefinition();
        $serviceSubscriberDefinition->setImplements($payload['implements']);

        Assert::assertSame($payload['implements'], $serviceSubscriberDefinition->getImplements());
    }
}
