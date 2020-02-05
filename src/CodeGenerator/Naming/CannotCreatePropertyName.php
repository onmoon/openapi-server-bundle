<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Naming;

use OnMoon\OpenApiServerBundle\Exception\OpenApiError;
use function Safe\sprintf;

final class CannotCreatePropertyName extends OpenApiError
{
    public static function becauseTextContaintsNoValidSymbols(string $text) : self
    {
        return new self(
            sprintf(
                'Cannot create property name from text: %s. Text contains no characters that can be used.',
                $text
            )
        );
    }

    public static function becauseIsNotValidPhpPropertyName(string $text) : self
    {
        return new self(
            sprintf(
                'Cannot create property name from: %s. String is not a valid PHP property name.',
                $text
            )
        );
    }
}
