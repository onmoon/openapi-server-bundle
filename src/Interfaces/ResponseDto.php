<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Interfaces;

interface ResponseDto extends Dto
{
    /**
     * @internal
     */
    public static function _getResponseCode() : int;
}
