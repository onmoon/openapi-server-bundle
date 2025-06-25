<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Exception;

use function sprintf;

final class CannotParseOpenApi extends OpenApiError
{
    /** @param array{method:string,url:string,path:string} $context */
    public static function becauseNoOperationIdSpecified(array $context): self
    {
        return new self(
            sprintf(
                'No operationId specified for operation: "%s" of path: "%s" in specification file: "%s".',
                $context['method'],
                $context['url'],
                $context['path']
            )
        );
    }

    public static function becausePropertyIsNotScheme(): self
    {
        return new self('Property is not scheme');
    }

    public static function becauseUnknownReferenceFound(string $name): self
    {
        return new self(sprintf('Component "%s does not exist"', $name));
    }

    /** @param array{method:string,url:string,path:string} $context */
    public static function becauseDuplicateOperationId(string $id, array $context): self
    {
        return new self(
            sprintf(
                'Operation ID "%s" already taken for operation: "%s" of path: "%s" in specification file: "%s".',
                $id,
                $context['method'],
                $context['url'],
                $context['path']
            )
        );
    }

    /** @param array{location:string,method:string,url:string,path:string} $context */
    public static function becauseRootIsNotObject(
        array $context,
        bool $isArray
    ): self {
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

    /** @param array{location:string,method:string,url:string,path:string} $context */
    public static function becauseOnlyScalarAreAllowed(string $propertyName, array $context): self
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

    /** @param array{location:string,method:string,url:string,path:string} $context */
    public static function becauseOpenapi31TypesNotSupported(string $propertyName, array $context): self
    {
        return new self(
            sprintf(
                'Cannot generate property for DTO class, property "%s" has multiple types in %s for operation: "%s" of path: "%s" in specification file: "%s".',
                $propertyName,
                $context['location'],
                $context['method'],
                $context['url'],
                $context['path']
            )
        );
    }

    /** @param array{location:string,method:string,url:string,path:string} $context */
    public static function becauseArrayIsNotDescribed(string $propertyName, array $context): self
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

    /** @param array{location:string,method:string,url:string,path:string} $context */
    public static function becauseTypeNotSupported(string $propertyName, string $type, array $context): self
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

    public static function becauseUnknownType(string $name): self
    {
        return new self(sprintf('Class "%s" does not exist', $name));
    }

    public static function becauseNotFQCN(string $name): self
    {
        return new self(sprintf('Class "%s" should have fully qualified name', $name));
    }
}
