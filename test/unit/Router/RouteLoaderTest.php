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
use PHPUnit\Framework\TestCase;
use Throwable;

use function count;

final class RouteLoaderTest extends TestCase
{
    private const SPECIFICATION_DEFAULT_NAME = 'CustomSpecification';

    private SpecificationLoader $specificationLoaderMock;
    private ArgumentResolver $argumentResolverMock;
    private Specification $specificationMock;
    private Operation $operationMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->specificationLoaderMock = $this->createMock(SpecificationLoader::class);
        $this->argumentResolverMock    = $this->createMock(ArgumentResolver::class);
        $this->specificationMock       = $this->createMock(Specification::class);
        $this->operationMock           = $this->createMock(Operation::class);
    }

    /**
     * @return string[]
     */
    public function loadProvider(): array
    {
        return [
            ['type' => null],
            ['type' => ''],
            [
                'type' => RouteLoader::OPENAPI_TYPE,
            ],
            [
                'type' => RouteLoader::OPENAPI_SPEC,
            ],
            [
                'type' => RouteLoader::OPENAPI_OPERATION,
            ],
        ];
    }

    /**
     * @throws Throwable
     *
     * @dataProvider loadProvider
     */
    public function testLoad(?string $type = null): void
    {
        $resource = self::SPECIFICATION_DEFAULT_NAME;

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
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->specificationMock);

        $this->specificationMock
            ->expects($this->once())
            ->method('getOperations')
            ->willReturn($operations);

        $this->operationMock
            ->expects($this->exactly($expectedOperationsCount))
            ->method('getRequestParameters')
            ->willReturn($requestParameters);

        $this->argumentResolverMock
            ->expects($this->exactly($expectedOperationsCount))
            ->method('resolveArgumentPatterns')
            ->with($requestParameters['path'])
            ->willReturn($requirements);

        $this->operationMock
            ->expects($this->exactly($expectedOperationsCount))
            ->method('getUrl')
            ->willReturn($operationUrl);

        $this->operationMock
            ->expects($this->exactly($expectedOperationsCount))
            ->method('getMethod')
            ->willReturn($operationMethod);

        $routeLoader = new RouteLoader(
            $this->specificationLoaderMock,
            $this->argumentResolverMock
        );

        $routeCollection = $routeLoader->load($resource, $type);

        Assert::assertCount(count($operations), $routeCollection);
    }

    /**
     * @return string[]
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
