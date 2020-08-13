<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use Exception;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Request;

use function array_key_exists;
use function array_map;
use function call_user_func;
use function is_callable;
use function is_resource;
use function Safe\json_decode;

final class ArrayDtoSerializer implements DtoSerializer
{
    private ScalarTypesResolver $resolver;
    private bool $sendNotRequiredNullableNulls;

    public function __construct(ScalarTypesResolver $resolver, bool $sendNulls)
    {
        $this->resolver                     = $resolver;
        $this->sendNotRequiredNullableNulls = $sendNulls;
    }

    /**
     * @param Dto|string $inputDtoFQCN
     *
     * @throws JsonException
     */
    public function createRequestDto(
        Request $request,
        Operation $operation,
        $inputDtoFQCN
    ): Dto {
        /** @var mixed[] $input */
        $input  = [];
        $params = $operation->getRequestParameters();
        if (array_key_exists('query', $params)) {
            /** @var string[] $source */
            $source                   = $request->query->all();
            $input['queryParameters'] = $this->convert(true, $source, $params['query']);
        }

        if (array_key_exists('path', $params)) {
            /** @var string[] $source */
            $source                  = (array) $request->attributes->get('_route_params', []);
            $input['pathParameters'] = $this->convert(true, $source, $params['path']);
        }

        $bodyType = $operation->getRequestBody();
        if ($bodyType !== null) {
            $source = $request->getContent();
            if (is_resource($source)) {
                throw new Exception('Expecting string as contents, resource received');
            }

            /** @var mixed[] $rawBody */
            $rawBody       = json_decode($source, true);
            $input['body'] = $this->convert(true, $rawBody, $bodyType);
        }

        $callable = [$inputDtoFQCN, 'fromArray'];
        if (! is_callable($callable)) {
            throw new Exception('Method not found');
        }

        /** @var Dto $inputDto */
        $inputDto = call_user_func($callable, $input);

        return $inputDto;
    }

    /** @inheritDoc */
    public function createResponseFromDto(ResponseDto $responseDto, Operation $operation): array
    {
        $statusCode = $responseDto::_getResponseCode();
        $source     = $responseDto->toArray();

        return $this->convert(false, $source, $operation->getResponse($statusCode));
    }

    /**
     * @param mixed[] $source
     *
     * @return mixed[]
     *
     * @psalm-suppress MissingClosureReturnType
     * @psalm-suppress MissingParamType
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedAssignment
     */
    private function convert(bool $deserialize, array $source, ObjectType $params): array
    {
        $result = [];
        foreach ($params->getProperties() as $property) {
            $name = $property->getName();
            if ($deserialize && ! array_key_exists($name, $source)) {
                $result[$name] = $property->getDefaultValue();
                continue;
            }

            if (! $deserialize && $source[$name] === null) {
                $value =  $property->getDefaultValue();
                if (
                    $property->isRequired() || $value !== null ||
                    ($this->sendNotRequiredNullableNulls && $property->isNullable())
                ) {
                    $result[$name] = $value;
                }

                continue;
            }

            $typeId     = $property->getScalarTypeId();
            $objectType = $property->getObjectTypeDefinition();

            if ($objectType !== null) {
                /** @psalm-suppress MissingClosureParamType */
                $converter = fn ($v) => $this->convert($deserialize, $v, $objectType);
            } else {
                /** @psalm-suppress MissingClosureParamType */
                $converter = fn ($v) => $this->resolver->convert($deserialize, $typeId ?? 0, $v);
            }

            if ($property->isArray()) {
                /** @psalm-suppress MissingClosureParamType */
                $converter = static fn ($v) => array_map(static fn ($i) => $converter($i), $v);
            }

            $result[$name] = $converter($source[$name]);
        }

        return $result;
    }
}
