<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Types;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use OnMoon\OpenApiServerBundle\Controller\ApiController;
use OnMoon\OpenApiServerBundle\DependencyInjection\CompilerPass;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Controller\ErrorController;

/**
 * @covers \OnMoon\OpenApiServerBundle\DependencyInjection\CompilerPass
 */
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

        $errorController = new Definition(ErrorController::class);
        $errorController->addTag('test');
        $this->setDefinition(ErrorController::class, $errorController);

        $this->compile();

        Assert::assertCount(1, $apiController->getMethodCalls());
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            ApiController::class,
            'setApiLoader',
            [new Reference(ApiController::class)]
        );
    }
}
