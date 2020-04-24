<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
/** phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse */
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Router\RouteLoader;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use ReflectionClass;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class ReflectionDtoSerializer implements DtoSerializer
{
    private ScalarTypesResolver $resolver;

    /**
     * ReflectionDtoSerializer constructor.
     * @param ScalarTypesResolver $resolver
     */
    public function __construct(ScalarTypesResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @psalm-param class-string<RequestHandler> $requestHandlerInterface
     */
    public function createRequestDto(
        Request $request,
        Route $route,
        string $requestHandlerInterface,
        string $methodName
    ) : ?Dto {
        /**
         * phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
         * @var class-string<Dto>|null $inputDtoFQCN
         */
        $inputDtoFQCN = $this->getInputDtoFQCN($requestHandlerInterface, $methodName);

        if ($inputDtoFQCN === null) {
            return null;
        }

        $input = [];
        if(method_exists($inputDtoFQCN, 'getQueryParameters')) {
            $input['queryParameters'] = $request->query->all();
        }
        if(method_exists($inputDtoFQCN, 'getPathParameters')) {
            $input['pathParameters'] = $this->getPathParameters($request, $route);
        }
        if(method_exists($inputDtoFQCN, 'getBody')) {
            $input['body'] = json_decode($request->getContent(), true);
        }

        /** @var Dto $inputDto */
        $inputDto = call_user_func($inputDtoFQCN.'::fromArray', $input);
        return $inputDto;
    }

    /** @return mixed[] */
    private function getPathParameters(Request $request, Route $route) : array {
        $source = (array) $request->attributes->get('_route_params', []);
        $result = [];
        /** @var int[] $params */
        $params = $route->getOption(RouteLoader::OPENAPI_ARGUMENTS);
        foreach ($params as $name => $typeId) {
            if(array_key_exists($name, $source)) {
                $result[$name] = $this->resolver->serialize($typeId, $source[$name]);
            }
        }
        return $result;
    }

    /**
     * @psalm-param class-string<RequestHandler> $requestHandlerInterface
     */
    private function getInputDtoFQCN(string $requestHandlerInterface, string $methodName) : ?string
    {
        $interfaceReflectionClass = new ReflectionClass($requestHandlerInterface);
        $method                   = $interfaceReflectionClass->getMethod($methodName);
        $methodParameters         = $method->getParameters();

        if (count($methodParameters) === 0) {
            return null;
        }

        $inputType = $methodParameters[0]->getType();
        assert($inputType instanceof ReflectionNamedType);

        return $inputType->getName();
    }
}
