<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Interfaces;

// phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
interface ResponseDto extends Dto
{
    public static function _getResponseCode(): string;
}
