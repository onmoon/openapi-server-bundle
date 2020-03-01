<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Exception;

use function Safe\sprintf;

class CannotMapToDto extends OpenApiError
{
    public static function becausePhpDocIsCorrupt(string $name, string $class) : self
    {
        return new self(
            sprintf(
                'PhpDoc is not readable for "%s" in "%s"',
                $name,
                $class
            )
        );
    }

    public static function becauseUnknownType(string $type, string $name, string $class) : self
    {
        return new self(
            sprintf(
                'Type "%s" is unknown for "%s" in "%s"',
                $type,
                $name,
                $class
            )
        );
    }

    public static function becauseClassDoesNotExist(string $childClass, string $name, string $class) : self
    {
        return new self(
            sprintf(
                'Class "%s" does not exist for "%s" in "%s"',
                $childClass,
                $name,
                $class
            )
        );
    }
}
