<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Exception;

use function Safe\sprintf;

final class CannotGenerateCodeForOperation extends OpenApiError
{
    public static function becauseNoOperationIdSpecified(
        string $url,
        string $method,
        string $specificationFilePath
    ) : self {
        return new self(
            sprintf(
                'No operationId specified for operation: "%s" of path: "%s" in specification file: "%s".',
                $method,
                $url,
                $specificationFilePath
            )
        );
    }

    public static function becauseRootIsNotObject(
        string $url,
        string $method,
        string $location,
        string $specificationFilePath
    ) : self {
        return new self(
            sprintf(
                'Only object is allowed as root in %s '.
                'for operation: "%s" of path: "%s" in specification file: "%s".',
                $location,
                $method,
                $url,
                $specificationFilePath
            )
        );
    }

    public static function becausePropertyNameIsReservedWord(string $propertyName) : self
    {
        return new self(
            sprintf(
                'Cannot generate property name for DTO class, property name: "%s" is a reserved word in PHP.',
                $propertyName
            )
        );
    }
}
