<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ClassGraphReadyEvent;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\Event\CodeGenerator\ClassGraphReadyEvent
 */
final class ClassGraphReadyEventTest extends TestCase
{
    public function testGraphMethodReturnGraph(): void
    {
        $graph = new GraphDefinition(
            [
                new SpecificationDefinition(
                    new SpecificationConfig('/', null, '/', 'application/json'),
                    [],
                    []
                ),
            ],
            new ServiceSubscriberDefinition()
        );

        $classGraphReadyEvent = new ClassGraphReadyEvent($graph);

        $return = $classGraphReadyEvent->graph();

        Assert::assertEquals($graph, $return);
    }
}
