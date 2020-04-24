<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use Exception;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
/** phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse */
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Router\RouteLoader;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use ReflectionClass;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use function array_key_exists;
use function assert;
use function call_user_func;
use function count;
use function is_resource;
use function method_exists;
use function Safe\json_decode;

class ReflectionDtoSerializer implements DtoSerializer
{
    private ScalarTypesResolver $resolver;

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

        /** @var mixed[] $input */
        $input = [];
        /** @var int[][] $params */
        $params = $route->getOption(RouteLoader::OPENAPI_ARGUMENTS);
        if (array_key_exists('query', $params)) {
            /** @var string[] $source */
            $source                   = $request->query->all();
            $input['queryParameters'] = $this->getParameters($source, $params['query']);
        }

        if (array_key_exists('path', $params)) {
            /** @var string[] $source */
            $source                  = (array) $request->attributes->get('_route_params', []);
            $input['pathParameters'] = $this->getParameters($source, $params['path']);
        }

        if (method_exists($inputDtoFQCN, 'getBody')) {
            $source = $request->getContent();
            if (is_resource($source)) {
                throw new Exception('Expecting string as contents, resource received');
            }

            /**
             * @psalm-suppress MixedAssignment
             */
            $input['body'] = json_decode($source, true);
        }

        /**
         * @phpstan-ignore-next-line
         * @var Dto $inputDto
         */
        $inputDto = call_user_func($inputDtoFQCN . '::fromArray', $input);

        return $inputDto;
    }

    /**
     * @param string[] $source
     * @param int[]    $params
     *
     * @return mixed[]
     */
    private function getParameters(array $source, array $params) : array
    {
        $result = [];
        foreach ($params as $name => $typeId) {
            if (! array_key_exists($name, $source)) {
                continue;
            }

            /** @psalm-suppress MixedAssignment */
            $result[$name] = $this->resolver->setType($typeId, $source[$name]);
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
