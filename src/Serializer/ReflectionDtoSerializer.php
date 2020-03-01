<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
/** phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse */
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use ReflectionClass;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Serializer\SerializerInterface;
use function assert;
use function count;

class ReflectionDtoSerializer implements DtoSerializer
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
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

        /** @var Dto $inputDto */
        $inputDto                = new $inputDtoFQCN();
        $inputDtoReflectionClass = new ReflectionClass($inputDto);

        $this->setRequestBody($inputDto, $inputDtoReflectionClass, $request);
        $this->setRequestPathParameters($inputDto, $inputDtoReflectionClass, $request);
        $this->setRequestQueryParameters($inputDto, $inputDtoReflectionClass, $request);

        return $inputDto;
    }

    public function createResponse(object $dto) : string
    {
        return $this->serializer->serialize($dto, 'json');
    }

    /**
     * @param ReflectionClass<Dto> $inputDtoRefl
     */
    private function setRequestQueryParameters(object $inputDto, ReflectionClass $inputDtoRefl, Request $request) : void
    {
        if (! $inputDtoRefl->hasProperty('queryParameters')) {
            return;
        }

        $dtoQueryParametersProperty = $inputDtoRefl->getProperty('queryParameters');
        /** @var ReflectionNamedType $dtoQueryParametersPropertyType */
        $dtoQueryParametersPropertyType = $dtoQueryParametersProperty->getType();
        /** @var class-string<Dto> $dtoQueryParametersPropertyTypeFQCN */
        $dtoQueryParametersPropertyTypeFQCN = $dtoQueryParametersPropertyType->getName();

        $queryParametersDto                = new $dtoQueryParametersPropertyTypeFQCN();
        $queryParametersDtoReflectionClass = new ReflectionClass($queryParametersDto);

        foreach ($queryParametersDtoReflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $property->setValue($queryParametersDto, $request->get($property->getName()));
        }

        $dtoQueryParametersProperty->setAccessible(true);
        $dtoQueryParametersProperty->setValue($inputDto, $queryParametersDto);
    }

    /**
     * @param ReflectionClass<Dto> $inputDtoRefl
     */
    private function setRequestPathParameters(object $inputDto, ReflectionClass $inputDtoRefl, Request $request) : void
    {
        /** @psalm-var array<string, string> $pathParameters */
        $pathParameters = (array) $request->attributes->get('_route_params', []);

        if (! $inputDtoRefl->hasProperty('pathParameters')) {
            return;
        }

        $dtoPathParametersProperty = $inputDtoRefl->getProperty('pathParameters');
        /** @var ReflectionNamedType $dtoPathParametersPropertyType */
        $dtoPathParametersPropertyType = $dtoPathParametersProperty->getType();
        /** @var class-string<Dto> $dtoPathParametersPropertyTypeFQCN */
        $dtoPathParametersPropertyTypeFQCN = $dtoPathParametersPropertyType->getName();

        $pathParametersDto                = new $dtoPathParametersPropertyTypeFQCN();
        $pathParametersDtoReflectionClass = new ReflectionClass($pathParametersDto);

        foreach ($pathParameters as $parameter => $value) {
            $parameterReflectionProperty = $pathParametersDtoReflectionClass->getProperty($parameter);

            $parameterReflectionProperty->setAccessible(true);
            $parameterReflectionProperty->setValue($pathParametersDto, $value);
        }

        $dtoPathParametersProperty->setAccessible(true);
        $dtoPathParametersProperty->setValue($inputDto, $pathParametersDto);
    }

    /**
     * @param ReflectionClass<Dto> $inputDtoRefl
     */
    private function setRequestBody(object $inputDto, ReflectionClass $inputDtoRefl, Request $request) : void
    {
        if (! $inputDtoRefl->hasProperty('body')) {
            return;
        }

        $dtoBodyProperty = $inputDtoRefl->getProperty('body');
        /** @var ReflectionNamedType $dtoBodyPropertyType */
        $dtoBodyPropertyType = $dtoBodyProperty->getType();
        /** @var class-string<Dto> $dtoBodyPropertyTypeFQCN */
        $dtoBodyPropertyTypeFQCN = $dtoBodyPropertyType->getName();

        $dtoBodyProperty->setAccessible(true);
        $dtoBodyProperty->setValue(
            $inputDto,
            $this->serializer->deserialize(
                $request->getContent(),
                $dtoBodyPropertyTypeFQCN,
                'json'
            )
        );
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
