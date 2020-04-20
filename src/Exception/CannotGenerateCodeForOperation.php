<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Exception;

use function Safe\sprintf;

final class CannotGenerateCodeForOperation extends OpenApiError
{
    public static function becauseNoOperationIdSpecified(array $context) : self {
        return new self(
            sprintf(
                'No operationId specified for operation: "%s" of path: "%s" in specification file: "%s".',
                $context['method'],
                $context['url'],
                $context['path']
            )
        );
    }

    public static function becauseRootIsNotObject(
        array $context,
        bool $isArray
    ) : self {
        $moreInfo = '';
        if ($isArray) {
            $moreInfo = '(array as root is insecure, see https://haacked.com/archive/2009/06/25/json-hijacking.aspx/) ';
        }

        return new self(
            sprintf(
                'Only object is allowed as root in %s ' . $moreInfo .
                'for operation: "%s" of path: "%s" in specification file: "%s".',
                $context['location'],
                $context['method'],
                $context['url'],
                $context['path']
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

    public static function becauseOnlyScalarAreAllowed(string $propertyName, array $context) : self
    {
        return new self(
            sprintf(
                'Cannot generate property for DTO class, property "%s" is not scalar in %s for operation: "%s" of path: "%s" in specification file: "%s".',
                $propertyName,
                $context['location'],
                $context['method'],
                $context['url'],
                $context['path']
            )
        );
    }

    public static function becauseArrayIsNotDescribed(string $propertyName, array $context) : self
    {
        return new self(
            sprintf(
                'Cannot generate property for DTO class, property "%s" is array without items description in %s for operation: "%s" of path: "%s" in specification file: "%s".',
                $propertyName,
                $context['location'],
                $context['method'],
                $context['url'],
                $context['path']
            )
        );
    }


    public static function becauseTypeNotSupported(string $propertyName, string $type, array $context) : self
    {
        return new self(
            sprintf(
                'Cannot generate property for DTO class, property "%s" type "%s" is not supported in %s for operation: "%s" of path: "%s" in specification file: "%s".',
                $propertyName,
                $type,
                $context['location'],
                $context['method'],
                $context['url'],
                $context['path']
            )
        );
    }
}
