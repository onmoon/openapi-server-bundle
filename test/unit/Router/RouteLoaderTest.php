<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Router;

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

final class RouteLoaderTest extends TestCase
{
    private const SPECIFICATION_DEFAULT_NAME = 'CustomSpecification';

    /** @var SpecificationLoader|MockObject */
    private $specificationLoaderMock;

    /** @var ArgumentResolver|MockObject */
    private $argumentResolverMock;

    /** @var Specification|MockObject */
    private $specificationMock;

    /** @var Operation|MockObject */
    private $operationMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->specificationLoaderMock = $this->createMock(SpecificationLoader::class);
        $this->argumentResolverMock    = $this->createMock(ArgumentResolver::class);
        $this->specificationMock       = $this->createMock(Specification::class);
        $this->operationMock           = $this->createMock(Operation::class);
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
        $operations = [
            'first' => $this->operationMock,
            'second' => $this->operationMock,
        ];

        $requestParameters = [
            'path' => new ObjectType([new Property('path')]),
        ];

        $operationUrl    = 'https://custom.local';
        $operationMethod = 'customAction';
        $requirements    = [];

        $expectedOperationsCount = count($operations);

        $this->specificationLoaderMock
            ->expects(self::once())
            ->method('load')
            ->willReturn($this->specificationMock);

        $this->specificationMock
            ->expects(self::once())
            ->method('getOperations')
            ->willReturn($operations);

        $this->operationMock
            ->expects(self::exactly($expectedOperationsCount))
            ->method('getRequestParameters')
            ->willReturn($requestParameters);

        $this->argumentResolverMock
            ->expects(self::exactly($expectedOperationsCount))
            ->method('resolveArgumentPatterns')
            ->with($requestParameters['path'])
            ->willReturn($requirements);

        $this->operationMock
            ->expects(self::exactly($expectedOperationsCount))
            ->method('getUrl')
            ->willReturn($operationUrl);

        $this->operationMock
            ->expects(self::exactly($expectedOperationsCount))
            ->method('getMethod')
            ->willReturn($operationMethod);

        $routeLoader = new RouteLoader(
            $this->specificationLoaderMock,
            $this->argumentResolverMock
        );

        $routeCollection = $routeLoader->load($resource, $type);

        foreach ($operations as $operationId => $operation) {
            $route = $routeCollection->get($operationId);

            if ($route === null) {
                continue;
            }

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
