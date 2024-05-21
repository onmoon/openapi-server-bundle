<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use OnMoon\OpenApiServerBundle\Controller\ApiController;
use OnMoon\OpenApiServerBundle\DependencyInjection\CompilerPass;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use function get_class;

/** @covers \OnMoon\OpenApiServerBundle\DependencyInjection\CompilerPass */
class CompilerPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CompilerPass('test'));
    }

    public function testProccess(): void
    {
        $apiController = new Definition(ApiController::class);
        $apiController->addTag('test');
        $this->setDefinition(ApiController::class, $apiController);

        $someRandomClass = new Definition(get_class(new class () {
        }));
        $someRandomClass->addTag('test');
        $this->setDefinition('some_random_class', $someRandomClass);

        $this->compile();

        Assert::assertCount(1, $apiController->getMethodCalls());
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            ApiController::class,
            'setApiLoader',
            [new Reference(ApiController::class)]
        );
    }
}
