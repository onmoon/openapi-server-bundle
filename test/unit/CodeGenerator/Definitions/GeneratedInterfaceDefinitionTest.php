<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedInterfaceDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedInterfaceDefinition
 */
final class GeneratedInterfaceDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function generatedInterfaceDefinitionProvider(): array
    {
        return [
            [
                'conditions' => ['hasExtends' => false],
            ],
            [
                'conditions' => ['hasExtends' => true],
            ],
        ];
    }

    /**
     * @param mixed[] $conditions
     *
     * @dataProvider generatedInterfaceDefinitionProvider
     */
    public function testGeneratedInterfaceDefinition(array $conditions): void
    {
        /** @var ClassDefinition|MockObject $classDefinitionMock */
        $classDefinitionMock = $this->createMock(ClassDefinition::class);

        $payload            = [];
        $payload['extends'] = (bool) $conditions['hasExtends'] ? $classDefinitionMock : null;

        $generatedInterfaceDefinition = new GeneratedInterfaceDefinition();

        $generatedInterfaceDefinition->setExtends($payload['extends']);

        Assert::assertSame($payload['extends'], $generatedInterfaceDefinition->getExtends());
    }
}
