<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use ReflectionClass;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Serializer\SerializerInterface;
use function count;

class ReflectionDtoSerializer implements DtoSerializer
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function createRequestDto(
        Request $request,
        Route $route,
        string $serviceInterface,
        string $methodName
    ) : ?object {
        $inputDtoFQCN = $this->getInputDtoFQCN($serviceInterface, $methodName);

        if ($inputDtoFQCN === null) {
            return null;
        }

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

    private function setRequestQueryParameters(object $inputDto, ReflectionClass $inputDtoRefl, Request $request)
    {
        if (! $inputDtoRefl->hasProperty('queryParameters')) {
            return;
        }

        $dtoQueryParametersProperty = $inputDtoRefl->getProperty('queryParameters');
        /** @var ReflectionNamedType $dtoQueryParametersPropertyType */
        $dtoQueryParametersPropertyType     = $dtoQueryParametersProperty->getType();
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

    private function setRequestPathParameters(object $inputDto, ReflectionClass $inputDtoRefl, Request $request) : void
    {
        $pathParameters = $request->attributes->get('_route_params') ?: [];

        if (! $inputDtoRefl->hasProperty('pathParameters')) {
            return;
        }

        $dtoPathParametersProperty = $inputDtoRefl->getProperty('pathParameters');
        /** @var ReflectionNamedType $dtoPathParametersPropertyType */
        $dtoPathParametersPropertyType     = $dtoPathParametersProperty->getType();
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

    private function setRequestBody(object $inputDto, ReflectionClass $inputDtoRefl, Request $request) : void
    {
        if (! $inputDtoRefl->hasProperty('body')) {
            return;
        }

        $dtoBodyProperty = $inputDtoRefl->getProperty('body');
        /** @var ReflectionNamedType $dtoBodyPropertyType */
        $dtoBodyPropertyType     = $dtoBodyProperty->getType();
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

    private function getInputDtoFQCN(string $serviceInterface, string $methodName) : ?string
    {
        $interfaceReflectionClass  = new ReflectionClass($serviceInterface);
        $method                    = $interfaceReflectionClass->getMethod($methodName);
        $methodParameters          = $method->getParameters();

        if (count($methodParameters) === 0) {
            return null;
        }

        /** @var ReflectionNamedType $inputType */
        $inputType = $methodParameters[0]->getType();

        return $inputType->getName();
    }
}
