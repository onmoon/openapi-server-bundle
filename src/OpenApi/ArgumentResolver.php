<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\OpenApi;

use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Schema;
use function preg_match;

class ArgumentResolver
{
    private ScalarTypesResolver $typesResolver;

    public function __construct(ScalarTypesResolver $typesResolver)
    {
        $this->typesResolver = $typesResolver;
    }

    /**
     * @param Parameter[] $pathParameters
     * @param Parameter[] $methodParameter
     *
     * @return mixed[]
     */
    public function resolveArgumentsTypeAndPattern(array $pathParameters, array $methodParameter) : array
    {
        $types    = [];
        $patterns = [];

        foreach ([$pathParameters, $methodParameter] as $parameters) {
            foreach ($parameters as $parameter) {
                if ($parameter->in !== 'path') {
                    continue;
                }

                if (! ($parameter->schema instanceof Schema)) {
                    continue;
                }

                $type                    = $this->typesResolver->findScalarType($parameter->schema);
                $types[$parameter->name] = $type;

                $schema  = $parameter->schema;
                $pattern = $this->typesResolver->getPattern($type);

                if (preg_match('/^\^(.*)\$$/', $schema->pattern, $matches)) {
                    $patterns[$parameter->name] = $matches[1];
                } elseif ($pattern) {
                    $patterns[$parameter->name] = $pattern;
                }
            }
        }

        return [$types, $patterns];
    }
}
