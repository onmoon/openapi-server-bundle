<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Types;

use Exception;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectSchema;

use function Safe\preg_match;

class ArgumentResolver
{
    private ScalarTypesResolver $typesResolver;

    public function __construct(ScalarTypesResolver $typesResolver)
    {
        $this->typesResolver = $typesResolver;
    }

    /**
     * @return string[]
     * @psalm-return array<string, (string|null)>
     */
    public function resolveArgumentPatterns(ObjectSchema $pathParameters): array
    {
        $patterns = [];

        foreach ($pathParameters->getProperties() as $parameter) {
            $type = $parameter->getScalarTypeId();
            if ($type === null) {
                throw new Exception('Object types are not supported in parameters');
            }

            $schemaPattern = $parameter->getPattern();
            $pattern       = $this->typesResolver->getPattern($type);

            if (
                $schemaPattern !== null &&
                preg_match('/^\^(.*)\$$/', $schemaPattern, $matches) === 1
            ) {
                /** @psalm-suppress PossiblyNullArrayAccess */
                $patterns[$parameter->getName()] = $matches[1];
            } elseif ($pattern !== null) {
                $patterns[$parameter->getName()] = $pattern;
            }
        }

        return $patterns;
    }
}
