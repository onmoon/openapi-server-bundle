<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use Exception;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use Symfony\Component\HttpFoundation\Request;
use function array_key_exists;
use function is_resource;
use function Safe\json_decode;

class ArrayDtoSerializer implements DtoSerializer
{
    private ScalarTypesResolver $resolver;

    public function __construct(ScalarTypesResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function createRequestDto(
        Request $request,
        Operation $operation,
        string $inputDtoFQCN
    ) : Dto {
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

        /**
         * @var Dto $inputDto
         */
        $inputDto = $inputDtoFQCN::{'fromArray'}($input);

        return $inputDto;
    }

    /** @inheritDoc */
    public function createResponseFromDto(ResponseDto $responseDto, Operation $operation) : array
    {
        $statusCode = $responseDto::_getResponseCode();
        $source     = $responseDto->toArray();

        return $this->convert(false, $source, $operation->getResponses()[$statusCode]);
    }

    /**
     * @param mixed[] $source
     *
     * @return mixed[]
     */
    private function convert(bool $deserialize, array $source, ObjectType $params) : array
    {
        $result = [];
        foreach ($params->getProperties() as $property) {
            $name = $property->getName();
            /** @psalm-var mixed $value */
            $value = $source[$name];

            if ($deserialize && !array_key_exists($name, $source)) {
                $result[$name] = $property->getDefaultValue();
                continue;
            }

            //ToDo: uncomment
            /*
            if (!$deserialize && $value === null && !$property->isRequired()) {
                continue;
            }
            */

            $typeId     = $property->getScalarTypeId();
            $objectType = $property->getObjectTypeDefinition();

            if ($objectType !== null) {
                $converter = fn($v) => $this->convert($deserialize, $v, $objectType);
            } else {
                $converter = fn($v) => $this->resolver->convert($deserialize, $typeId??0, $v);
            }

            if($property->isArray()) {
                $converter = fn($v) => array_map(fn ($i) => $converter($i), $v);
            }

            $result[$name] = $converter($value);
        }

        return $result;
    }
}
