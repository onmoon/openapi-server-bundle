<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Exception;

use function Safe\sprintf;

class ApiCallFailed extends OpenApiError
{
    public static function becauseApiLoaderNotFound() : self
    {
        return new self('ApiLoader not found. Try re-generating code');
    }

    public static function becauseNotImplemented(string $interface) : self
    {
        return new self(
            sprintf(
                'Api call implementation not found. Please implement "%s" interface',
                $interface
            )
        );
    }
}
