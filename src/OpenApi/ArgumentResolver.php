<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\OpenApi;

use cebe\openapi\spec\Parameter;

class ArgumentResolver
{
    private ScalarTypesResolver $typesResolver;

    /**
     * PathParameterResolver constructor.
     * @param ScalarTypesResolver $typesResolver
     */
    public function __construct(ScalarTypesResolver $typesResolver)
    {
        $this->typesResolver = $typesResolver;
    }

    /**
     * @param Parameter[] $pathParameters
     * @param Parameter[] $methodParameter
     * @return array
     */
    public function resolveArgumentsTypeAndPattern($pathParameters, $methodParameter): array {
        $types = [];
        $patterns = [];
        foreach ([$pathParameters, $methodParameter] as $parameters) {
            foreach ($parameters as $parameter) {
                if ($parameter->in !== 'path') {
                    continue;
                }
                $type = $this->typesResolver->findScalarType($parameter->schema);
                $types[$parameter->name] = $type;

                if(!is_null($schema = $parameter->schema) and !is_null($schema->pattern)
                    and preg_match('/^\^(.*)\$$/', $schema->pattern, $matches)) {
                    $patterns[$parameter->name] = $matches[1];
                } elseif($pattern = $this->typesResolver->getPattern($type)) {
                    $patterns[$parameter->name] = $pattern;
                }

            }
        }

        return [$types, $patterns];
    }

}