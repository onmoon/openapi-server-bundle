<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use function str_replace;

use const DIRECTORY_SEPARATOR;

/** @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition */
final class GraphDefinitionTest extends TestCase
{
    /** @return mixed[] */
    public static function graphDefinitionProvider(): array
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
        $specificationDefinition     = new SpecificationDefinition(
            new SpecificationConfig(
                str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/path'),
                null,
                'Some\Namespace',
                'some/media-type'
            ),
            [],
            []
        );
        $serviceSubscriberDefinition = new ServiceSubscriberDefinition();

        $payload                      = [];
        $payload['specifications']    = (bool) $conditions['hasSpecifications'] ? [$specificationDefinition] : [];
        $payload['serviceSubscriber'] = $serviceSubscriberDefinition;

        $generatedInterfaceDefinition = new GraphDefinition(
            $payload['specifications'],
            $payload['serviceSubscriber']
        );

        Assert::assertSame($payload['specifications'], $generatedInterfaceDefinition->getSpecifications());
        Assert::assertSame($payload['serviceSubscriber'], $generatedInterfaceDefinition->getServiceSubscriber());
    }
}
