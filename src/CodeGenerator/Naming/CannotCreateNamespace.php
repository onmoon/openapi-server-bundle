<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Naming;

use OnMoon\OpenApiServerBundle\Exception\OpenApiError;

final class CannotCreateNamespace extends OpenApiError
{
    public static function becauseTextContainsNoValidSymbols(string $text): self
    {
        return new self(
            sprintf(
                'Cannot create namespace from text: %s. Text contains no characters that can be used.',
                $text
            )
        );
    }
}
