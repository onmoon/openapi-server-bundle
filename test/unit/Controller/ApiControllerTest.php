<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator;

use cebe\openapi\spec\OpenApi;
use Exception;
use OnMoon\OpenApiServerBundle\Controller\ApiController;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Interfaces\SetClientIp;
use OnMoon\OpenApiServerBundle\Interfaces\SetRequest;
use OnMoon\OpenApiServerBundle\Serializer\DtoSerializer;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Validator\RequestSchemaValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

use function getcwd;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\AttributeGenerator
 */
class ApiControllerTest extends TestCase
{
    private SpecificationLoader $specificationLoader;
    private RouterInterface $router;
    private DtoSerializer $serializer;
    private EventDispatcherInterface $eventDispatcher;
    private RequestSchemaValidator $requestValidator;

    public function setUp(): void
    {
        $this->specificationLoader = $this->createMock(SpecificationLoader::class);
        $this->router              = $this->createMock(RouterInterface::class);
        $this->serializer          = $this->createMock(DtoSerializer::class);
        $this->eventDispatcher     = $this->createMock(EventDispatcherInterface::class);
        $this->requestValidator    = $this->createMock(RequestSchemaValidator::class);
    }

    public function testHandleWithoutRouteThrowsException(): void
    {
        $this->router->method('getRouteCollection')->willReturn(new RouteCollection());

        $apiController = new ApiController(
            $this->specificationLoader,
            $this->router,
            $this->serializer,
            $this->eventDispatcher,
            $this->requestValidator
        );

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Route not found');

        $request = new Request(
            [],
            [],
            ['_route_params' => '', '_route' => 'test'],
        );
        $apiController->handle($request);
    }

    public function testHandleWithNullApiLoaderThrowsException(): void
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('test', new Route('/test', [], [], ['_openapi_operation' => 'test_option']));
        $this->router->method('getRouteCollection')->willReturn($routeCollection);
        $this->specificationLoader->method('load')->willReturn(new Specification(['test_option' => new Operation('/', 'test', 'name')], new OpenApi([])));

        $apiController = new ApiController(
            $this->specificationLoader,
            $this->router,
            $this->serializer,
            $this->eventDispatcher,
            $this->requestValidator
        );

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('ApiLoader not found. Try re-generating code');

        $request = new Request(
            [],
            [],
            ['_route_params' => '', '_route' => 'test'],
        );
        $apiController->handle($request);
    }

    public function testHandleWithNotImplementedRequestThrowsException(): void
    {
        $apiLoader = new class implements ApiLoader {
            public function get(string $interfaceName): ?RequestHandler
            {
                return null;
            }

            /**
             * @return string[]
             */
            public static function getSubscribedServices(): array
            {
                return ['operation_name' => 'test_function_string'];
            }
        };

        $routeCollection = new RouteCollection();
        $routeCollection->add('test', new Route('/test', [], [], ['_openapi_operation' => 'test_option']));
        $this->router->method('getRouteCollection')->willReturn($routeCollection);
        $this->specificationLoader->method('load')->willReturn(new Specification(['test_option' => new Operation('/', 'test', 'operation_name')], new OpenApi([])));

        $apiController = new ApiController(
            $this->specificationLoader,
            $this->router,
            $this->serializer,
            $this->eventDispatcher,
            $this->requestValidator
        );
        $apiController->setApiLoader($apiLoader);

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Api call implementation not found. Please implement "test_function_string" interface');

        $request = new Request(
            [],
            [],
            ['_route_params' => '', '_route' => 'test'],
        );
        $apiController->handle($request);
    }

    public function testHandleWithSetRequestRequestHandler(): void
    {
        $apiLoader = new class implements ApiLoader {
            public function get(string $interfaceName): ?RequestHandler
            {
                return new class implements SetRequest, RequestHandler {
                    public function setRequest(Request $request): void
                    {
                        throw new Exception('test request was set');
                    }
                };
            }

            /**
             * @return string[]
             */
            public static function getSubscribedServices(): array
            {
                return ['operation_name' => 'test_function_string'];
            }
        };

        $routeCollection = new RouteCollection();
        $routeCollection->add('test', new Route('/test', [], [], ['_openapi_operation' => 'test_option']));
        $this->router->method('getRouteCollection')->willReturn($routeCollection);
        $this->specificationLoader->method('load')->willReturn(new Specification(['test_option' => new Operation('/', 'test', 'operation_name')], new OpenApi([])));

        $apiController = new ApiController(
            $this->specificationLoader,
            $this->router,
            $this->serializer,
            $this->eventDispatcher,
            $this->requestValidator
        );
        $apiController->setApiLoader($apiLoader);

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('test request was set');

        $request = new Request(
            [],
            [],
            ['_route_params' => '', '_route' => 'test'],
        );
        $apiController->handle($request);
    }

    public function testHandleWithSetClientIpRequestHandler(): void
    {
        $apiLoader = new class implements ApiLoader {
            public function get(string $interfaceName): ?RequestHandler
            {
                return new class implements SetClientIp, RequestHandler {
                    public function setClientIp(string $ip): void
                    {
                        throw new Exception('test ip was set');
                    }
                };
            }

            /**
             * @return string[]
             */
            public static function getSubscribedServices(): array
            {
                return ['operation_name' => 'test_function_string'];
            }
        };

        $routeCollection = new RouteCollection();
        $routeCollection->add('test', new Route('/test', [], [], ['_openapi_operation' => 'test_option']));
        $this->router->method('getRouteCollection')->willReturn($routeCollection);
        $this->specificationLoader->method('load')->willReturn(new Specification(['test_option' => new Operation('/', 'test', 'operation_name')], new OpenApi([])));

        $apiController = new ApiController(
            $this->specificationLoader,
            $this->router,
            $this->serializer,
            $this->eventDispatcher,
            $this->requestValidator
        );
        $apiController->setApiLoader($apiLoader);

        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('test ip was set');

        $request = new Request(
            [],
            [],
            ['_route_params' => '', '_route' => 'test'],
        );
        $apiController->handle($request);
    }

    public function testHandleWithClassWithoutPublicMethodsImplementationThrowsException(): void
    {
        require getcwd() . '/test/unit/Controller/test_class_without_public_methods.php';

        $apiLoader = new class implements ApiLoader {
            public function get(string $interfaceName): ?RequestHandler
            {
                return new class implements RequestHandler {
                };
            }

            /**
             * @return string[]
             */
            public static function getSubscribedServices(): array
            {
                return ['operation_name' => 'TestClass'];
            }
        };

        $routeCollection = new RouteCollection();
        $routeCollection->add('test', new Route('/test', [], [], ['_openapi_operation' => 'test_option']));
        $this->router->method('getRouteCollection')->willReturn($routeCollection);
        $this->specificationLoader->method('load')->willReturn(new Specification(['test_option' => new Operation('/', 'test', 'operation_name')], new OpenApi([])));

        $apiController = new ApiController(
            $this->specificationLoader,
            $this->router,
            $this->serializer,
            $this->eventDispatcher,
            $this->requestValidator
        );
        $apiController->setApiLoader($apiLoader);

        $this->expectException(Throwable::class);
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('"test_class" has 0 public methods, exactly one expected');

        $request = new Request(
            [],
            [],
            ['_route_params' => '', '_route' => 'test'],
        );
        $apiController->handle($request);
    }

    public function testHandleWithClassWithoutTypedParamThrowsException(): void
    {
        require getcwd() . '/test/unit/Controller/test_class_without_typed_param.php';

        $apiLoader = new class implements ApiLoader {
            public function get(string $interfaceName): ?RequestHandler
            {
                return new class implements RequestHandler {
                };
            }

            /**
             * @return string[]
             */
            public static function getSubscribedServices(): array
            {
                return ['operation_name' => 'TestClass2'];
            }
        };

        $routeCollection = new RouteCollection();
        $routeCollection->add('test', new Route('/test', [], [], ['_openapi_operation' => 'test_option']));
        $this->router->method('getRouteCollection')->willReturn($routeCollection);
        $this->specificationLoader->method('load')->willReturn(new Specification(['test_option' => new Operation('/', 'test', 'operation_name')], new OpenApi([])));

        $apiController = new ApiController(
            $this->specificationLoader,
            $this->router,
            $this->serializer,
            $this->eventDispatcher,
            $this->requestValidator
        );
        $apiController->setApiLoader($apiLoader);

        $this->expectException(Throwable::class);
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Input parameter for test_class2 is not a named type');

        $request = new Request(
            [],
            [],
            ['_route_params' => '', '_route' => 'test'],
        );
        $apiController->handle($request);
    }
}
