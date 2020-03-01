<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Validator;

use cebe\openapi\spec\OpenApi;
use Symfony\Component\HttpFoundation\Request;

interface RequestSchemaValidator
{
    public function validate(Request $request, OpenApi $specification, string $path, string $method) : void;
}
