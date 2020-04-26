<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Validator;

use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use Symfony\Component\HttpFoundation\Request;

interface RequestSchemaValidator
{
    public function validate(Request $request, Specification $specification, string $operationId) : void;
}
