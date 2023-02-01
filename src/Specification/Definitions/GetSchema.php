<?php

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

interface GetSchema
{
    public function getSchema(): ObjectSchema;
}