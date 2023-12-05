<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use Exception;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectSchema;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use Symfony\Component\HttpFoundation\Request;

use function array_key_exists;
use function array_map;
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

    public function createRequestDto(
        Request $request,
        Operation $operation,
        string $inputDtoFQCN
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
            /** @var resource|string $source */
            $source = $request->getContent();
            if (is_resource($source)) {
                throw new Exception('Expecting string as contents, resource received');
            }

            /** @var mixed[] $rawBody */
            $rawBody       = json_decode($source, true);
            $input['body'] = $this->convert(true, $rawBody, $bodyType->getSchema());
        }

        /**
         * @var Dto $inputDto
         */
        $inputDto = $inputDtoFQCN::{'fromArray'}($input);

        return $inputDto;
    }

    /** @inheritDoc */
    public function createResponseFromDto(Dto $responseDto, ObjectSchema $definition): array
    {
        return $this->convert(false, $responseDto->toArray(), $definition);
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
    private function convert(bool $deserialize, array $source, ObjectSchema $params): array
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
                $converter = fn ($v) => $this->convert($deserialize, $v, $objectType->getSchema());
            } else {
                $outputClass = $property->getOutputType();

                /** @psalm-suppress MissingClosureParamType */
                $converter = fn ($v) => $this->resolver->convert($deserialize, $typeId ?? 0, $v, $outputClass);
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
