<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Controller;

use OnMoon\OpenApiServerBundle\Command\GenerateApiCodeCommand;
use OnMoon\OpenApiServerBundle\Controller\ApiController;
use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;


/**
 * @covers \OnMoon\OpenApiServerBundle\Controller\ApiController
 */
class ApiControllerTest extends WebTestCase
{
    protected static $class = TestKernel::class;

    public function testGetApiReturnsOkRequest(): void
    {
        /** @var HttpBrowser $client */
        $client        = static::createClient();
        $application   = new Application(static::$kernel);
        $command       = $application->find(GenerateApiCodeCommand::COMMAND);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => GenerateApiCodeCommand::COMMAND]);

        $container = static::$container->get(ContainerInterface::class);

        $container->set('petstore.getGood', new GetGoodImpl());

        $apiLoaderClass = TestKernel::$bundleRootNamespace.'\ServiceSubscriber\ApiServiceLoaderServiceSubscriber';
        $apiLoader      = new $apiLoaderClass($container);
        static::$container->get(ApiController::class)->setApiLoader($apiLoader);

        $client->request(
            'GET',
            '/api/goods/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        Assert::assertTrue($client->getResponse()->isSuccessful());
        Assert::assertSame($client->getResponse()->getStatusCode(),200);

        $filesystem = new Filesystem();
        $filesystem->remove([TestKernel::$bundleRootPath]);
    }

}
