<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use Exception;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
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
            $input['queryParameters'] = $this->getParameters($source, $params['query']);
        }

        if (array_key_exists('path', $params)) {
            /** @var string[] $source */
            $source                  = (array) $request->attributes->get('_route_params', []);
            $input['pathParameters'] = $this->getParameters($source, $params['path']);
        }

        if ($operation->getRequestBody() !== null) {
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
         * @var Dto $inputDto
         */
        $inputDto = $inputDtoFQCN::{'fromArray'}($input);

        return $inputDto;
    }

    /**
     * @param string[] $source
     *
     * @return mixed[]
     */
    private function getParameters(array $source, ObjectType $params) : array
    {
        $result = [];
        foreach ($params->getProperties() as $property) {
            $name = $property->getName();
            if (! array_key_exists($name, $source)) {
                continue;
            }

            $typeId = $property->getScalarTypeId();
            if ($typeId === null) {
                throw new Exception('Non scalar property is not supported');
            }

            /** @psalm-suppress MixedAssignment */
            $result[$name] = $this->resolver->setType($typeId, $source[$name]);
        }

        return $result;
    }
}
