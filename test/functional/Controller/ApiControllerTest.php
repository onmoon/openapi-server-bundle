<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Controller;

use OnMoon\OpenApiServerBundle\Command\GenerateApiCodeCommand;
use OnMoon\OpenApiServerBundle\Controller\ApiController;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

use function Safe\file_put_contents;
use function Safe\json_decode;

use const DIRECTORY_SEPARATOR;

/**
 * @covers \OnMoon\OpenApiServerBundle\Controller\ApiController
 */
class ApiControllerTest extends WebTestCase
{
    private AbstractBrowser $client;

    public function setUp(): void
    {
        /** @var HttpBrowser $client */
        $client = static::createClient();

        $this->client  = $client;
        $application   = new Application(static::$kernel);
        $command       = $application->find(GenerateApiCodeCommand::COMMAND);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => GenerateApiCodeCommand::COMMAND]);
        $getGoodImplClassName = $this->createGetGoodImpl();

        static::getContainer()->set('petstore.getGood', new $getGoodImplClassName());

        $apiLoaderClass = TestKernel::$bundleRootNamespace . '\ServiceSubscriber\ApiServiceLoaderServiceSubscriber';
        /** @var ApiLoader $apiLoader */
        $apiLoader = new $apiLoaderClass(static::getContainer());

        /** @var ApiController $apiController */
        $apiController = static::getContainer()->get(ApiController::class);
        $apiController->setApiLoader($apiLoader);
    }

    public function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove([TestKernel::$bundleRootPath]);
        unset($this->client);
        parent::tearDown();
    }

    public function testGetApiReturnsOkRequest(): void
    {
        $this->client->request(
            'GET',
            '/api/goods/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        /** @var Response $response */
        $response = $this->client->getResponse();

        Assert::assertTrue($response->isSuccessful());
        Assert::assertSame($response->getStatusCode(), 200);
        Assert::assertEquals(['title' => 'test'], json_decode((string) $response->getContent(), true));
    }

    /**
     * {@inheritDoc}
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new class ('test', true) extends TestKernel {
            protected function configureContainer(ContainerConfigurator $c): void
            {
            }

            protected function configureRoutes(RoutingConfigurator $routes): void
            {
                $routes->import(__DIR__ . DIRECTORY_SEPARATOR . 'openapi_routes.yaml');
            }
        };
    }

    private function createGetGoodImpl(): string
    {
        $content = <<<EOD
<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Generated;

use OnMoon\OpenApiServerBundle\Test\Functional\Generated\Apis\PetStore\GetGood\Dto\Request\GetGoodRequestDto;
use OnMoon\OpenApiServerBundle\Test\Functional\Generated\Components\PetStore\GoodResponseSchema\GoodResponseSchema;
use OnMoon\OpenApiServerBundle\Test\Functional\Generated\Apis\PetStore\GetGood\GetGood;

class GetGoodImpl implements GetGood
{
    public function getGood(GetGoodRequestDto \$request): GoodResponseSchema
    {
        return new GoodResponseSchema('test');
    }
}
EOD;

        file_put_contents(TestKernel::$bundleRootPath . DIRECTORY_SEPARATOR . 'GetGoodImpl.php', $content);

        return TestKernel::$bundleRootNamespace . '\GetGoodImpl';
    }
}
