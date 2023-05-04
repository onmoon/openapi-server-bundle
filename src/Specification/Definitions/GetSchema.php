<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

interface GetSchema
{
    public function getSchema(): ObjectSchema;
}
