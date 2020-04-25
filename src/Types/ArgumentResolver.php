<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Types;

use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use function Safe\preg_match;

class ArgumentResolver
{
    private ScalarTypesResolver $typesResolver;

    public function __construct(ScalarTypesResolver $typesResolver)
    {
        $this->typesResolver = $typesResolver;
    }

    /**
     * @param ObjectType[] $parameters
     *
     * @return mixed[]
     *
     * @psalm-param array<string, ObjectType> $parameters
     */
    public function resolveArgumentsTypeAndPattern(array $parameters) : array
    {
        $types    = [];
        $patterns = [];

        foreach ($parameters as $in => $parametersObject) {
            foreach ($parametersObject->getProperties() as $parameter) {
                $type = $parameter->getScalarTypeId();
                if($type === null) {
                    throw new \Exception('Object types are not supported in parameters');
                }
                $types[$in][$parameter->getName()] = $type;

                if ($in !== 'path') {
                    continue;
                }

                $schemaPattern  = $parameter->getPattern();
                $pattern = $this->typesResolver->getPattern($type);

                if ($schemaPattern !== null &&
                    preg_match('/^\^(.*)\$$/', $schemaPattern, $matches)
                ) {
                    /** @psalm-suppress PossiblyNullArrayAccess */
                    $patterns[$parameter->getName()] = (string) $matches[1];
                } elseif ($pattern) {
                    $patterns[$parameter->getName()] = $pattern;
                }
            }
        }

        return [$types, $patterns];
    }
}
