<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Router;

use cebe\openapi\spec\OpenApi;
use OnMoon\OpenApiServerBundle\Router\RouteLoader;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Types\ArgumentResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

use function count;

/**
 * @covers \OnMoon\OpenApiServerBundle\Router\RouteLoader
 */
final class RouteLoaderTest extends TestCase
{
    private const SPECIFICATION_DEFAULT_NAME = 'CustomSpecification';

    /** @var SpecificationLoader|MockObject */
    private $specificationLoaderMock;
    /** @var ArgumentResolver|MockObject */
    private $argumentResolverMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->specificationLoaderMock = $this->createMock(SpecificationLoader::class);
        $this->argumentResolverMock    = $this->createMock(ArgumentResolver::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->specificationLoaderMock,
            $this->argumentResolverMock
        );

        parent::tearDown();
    }

    /**
     * @return mixed[]
     */
    public function loadProvider(): array
    {
        return [
            [
                'resource' => 1,
                'type' => RouteLoader::OPENAPI_TYPE,
            ],
            [
                'resource' => self::SPECIFICATION_DEFAULT_NAME,
                'type' => null,
            ],
            [
                'resource' => self::SPECIFICATION_DEFAULT_NAME,
                'type' => '',
            ],
            [
                'resource' => self::SPECIFICATION_DEFAULT_NAME,
                'type' => RouteLoader::OPENAPI_TYPE,
            ],
            [
                'resource' => self::SPECIFICATION_DEFAULT_NAME,
                'type' => RouteLoader::OPENAPI_SPEC,
            ],
            [
                'resource' => self::SPECIFICATION_DEFAULT_NAME,
                'type' => RouteLoader::OPENAPI_OPERATION,
            ],
        ];
    }

    /**
     * @param mixed $resource
     *
     * @throws Throwable
     *
     * @dataProvider loadProvider
     */
    public function testLoad($resource, ?string $type = null): void
    {
        $operationUrl                = '/some/example/path';
        $operationMethod             = 'GET';
        $operationRequestHandlerName = 'RequestHandlerExample';
        $operationRequestParameters  = [
            'path' => new ObjectType([new Property('path')]),
        ];

        $requirements = [];

        $operations = [
            'first' => new Operation(
                $operationUrl,
                $operationMethod,
                $operationRequestHandlerName,
                null,
                null,
                $operationRequestParameters
            ),
        ];

        $expectedOperationsCount = count($operations);

        $specification = new Specification($operations, new OpenApi([]));

        $this->specificationLoaderMock
            ->expects(self::once())
            ->method('load')
            ->with($resource)
            ->willReturn($specification);

        $this->argumentResolverMock
            ->expects(self::exactly($expectedOperationsCount))
            ->method('resolveArgumentPatterns')
            ->with($operationRequestParameters['path'])
            ->willReturn($requirements);

        $routeLoader = new RouteLoader(
            $this->specificationLoaderMock,
            $this->argumentResolverMock
        );

        $routeCollection = $routeLoader->load($resource, $type);

        foreach ($operations as $operationId => $operation) {
            $route = $routeCollection->get($operationId);

            Assert::assertNotNull($route);
            Assert::assertSame(
                'OnMoon\OpenApiServerBundle\Controller\ApiController::handle',
                $route->getDefault('_controller')
            );
            Assert::assertSame(
                (string) $resource,
                $route->getOption(RouteLoader::OPENAPI_SPEC)
            );
            Assert::assertSame(
                $operationId,
                $route->getOption(RouteLoader::OPENAPI_OPERATION)
            );

            Assert::assertSame($route->getPath(), $operationUrl);
            Assert::assertSame($route->getRequirements(), $requirements);
            Assert::assertEmpty($route->getSchemes());
            Assert::assertEmpty($route->getHost());
            Assert::assertSame($route->getMethods(), [$operationMethod]);
        }

        Assert::assertCount(count($operations), $routeCollection);
    }

    /**
     * @return mixed[]
     */
    public function supportsProvider(): array
    {
        return [
            [
                'type' => null,
                'expected' => false,
            ],
            [
                'type' => '',
                'expected' => false,
            ],
            [
                'type' => RouteLoader::OPENAPI_TYPE,
                'expected' => true,
            ],
            [
                'type' => RouteLoader::OPENAPI_SPEC,
                'expected' => false,
            ],
            [
                'type' => RouteLoader::OPENAPI_OPERATION,
                'expected' => false,
            ],
        ];
    }

    /**
     * @throws Throwable
     *
     * @dataProvider supportsProvider
     */
    public function testSupports(?string $type = null, bool $expected): void
    {
        $resource = self::SPECIFICATION_DEFAULT_NAME;

        $routeLoader = new RouteLoader(
            $this->specificationLoaderMock,
            $this->argumentResolverMock
        );

        $isSupportsResult = $routeLoader->supports($resource, $type);

        Assert::assertSame($expected, $isSupportsResult);
    }
}
