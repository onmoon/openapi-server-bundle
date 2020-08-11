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
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

use function Safe\file_put_contents;

/**
 * @covers \OnMoon\OpenApiServerBundle\Controller\ApiController
 */
class ApiControllerTest extends WebTestCase
{
    /**
     * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string $class
     */
    protected static $class = TestKernel::class;

    public function testGetApiReturnsOkRequest(): void
    {
        /** @var HttpBrowser $client */
        $client        = static::createClient();
        $application   = new Application(static::$kernel);
        $command       = $application->find(GenerateApiCodeCommand::COMMAND);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => GenerateApiCodeCommand::COMMAND]);
        $getGoodImplClassName = $this->createGetGoodImpl();

        static::$container->set('petstore.getGood', new $getGoodImplClassName());

        $apiLoaderClass = TestKernel::$bundleRootNamespace . '\ServiceSubscriber\ApiServiceLoaderServiceSubscriber';
        /** @var ApiLoader $apiLoader */
        $apiLoader = new $apiLoaderClass(static::$container);

        /** @var ApiController $apiController */
        $apiController = static::$container->get(ApiController::class);
        $apiController->setApiLoader($apiLoader);

        $client->request(
            'GET',
            '/api/goods/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        /** @var Response $response */
        $response = $client->getResponse();

        Assert::assertTrue($response->isSuccessful());
        Assert::assertSame($response->getStatusCode(), 200);

        $filesystem = new Filesystem();
        $filesystem->remove([TestKernel::$bundleRootPath]);
    }

    private function createGetGoodImpl(): string
    {
        $content = <<<EOD
<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Generated;

use OnMoon\OpenApiServerBundle\Test\Functional\Generated\Apis\PetStore\GetGood\Dto\Request\GetGoodRequestDto;
use OnMoon\OpenApiServerBundle\Test\Functional\Generated\Apis\PetStore\GetGood\Dto\Response\OK\GetGoodOKDto;
use OnMoon\OpenApiServerBundle\Test\Functional\Generated\Apis\PetStore\GetGood\GetGood;

class GetGoodImpl implements GetGood
{
    public function getGood(GetGoodRequestDto \$request): GetGoodOKDto
    {
        return new GetGoodOKDto('test');
    }
}
EOD;

        file_put_contents(TestKernel::$bundleRootPath . '/GetGoodImpl.php', $content);

        return TestKernel::$bundleRootNamespace . '\GetGoodImpl';
    }
}
