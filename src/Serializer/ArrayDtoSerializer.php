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
            $input['queryParameters'] = $this->deserialize($source, $params['query']);
        }

        if (array_key_exists('path', $params)) {
            /** @var string[] $source */
            $source                  = (array) $request->attributes->get('_route_params', []);
            $input['pathParameters'] = $this->deserialize($source, $params['path']);
        }

        $bodyType = $operation->getRequestBody();
        if ($bodyType !== null) {
            $source = $request->getContent();
            if (is_resource($source)) {
                throw new Exception('Expecting string as contents, resource received');
            }

            $rawBody       = json_decode($source, true);
            $input['body'] = $this->deserialize($rawBody, $bodyType);
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

        return $this->serialize($source, $operation->getResponses()[$statusCode]);
    }

    /**
     * @param mixed[] $source
     *
     * @return mixed[]
     */
    private function deserialize(array $source, ObjectType $params) : array
    {
        $result = [];
        foreach ($params->getProperties() as $property) {
            $name = $property->getName();
            if (! array_key_exists($name, $source)) {
                $result[$name] = $property->getDefaultValue();
                continue;
            }

            $typeId     = $property->getScalarTypeId();
            $objectType = $property->getObjectTypeDefinition();
            if ($typeId !== null) {
                /** @psalm-suppress MixedAssignment */
                $result[$name] = $this->resolver->deserialize($typeId, $source[$name]);
            } elseif ($objectType !== null) {
                $result[$name] = $this->deserialize($source[$name], $objectType);
            }
        }

        return $result;
    }

    /**
     * @param mixed[] $source
     *
     * @return mixed[]
     */
    private function serialize(array $source, ObjectType $params) : array
    {
        $result = [];
        foreach ($params->getProperties() as $property) {
            $name  = $property->getName();
            $value = $source[$name];

            //ToDo: uncomment
            /*
            if ($value === null && !$property->isRequired()) {
                continue;
            }
            */

            $typeId     = $property->getScalarTypeId();
            $objectType = $property->getObjectTypeDefinition();
            if ($typeId !== null) {
                /** @psalm-suppress MixedAssignment */
                $result[$name] = $this->resolver->serialize($typeId, $value);
            } elseif ($objectType !== null) {
                $result[$name] = $this->serialize($value, $objectType);
            }
        }

        return $result;
    }
}
