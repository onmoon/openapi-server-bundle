<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Exception;

use function implode;
use function Safe\sprintf;

final class ApiCallFailed extends OpenApiError
{
    public static function becauseApiLoaderNotFound(): self
    {
        return new self('ApiLoader not found. Try re-generating code');
    }

    public static function becauseNotImplemented(string $interface): self
    {
        return new self(
            sprintf(
                'Api call implementation not found. Please implement "%s" interface',
                $interface
            )
        );
    }

    public static function becauseNoResponseCodeSet(): self
    {
        return new self('Response type is ambiguous, please set response code manually');
    }

    /** @param string[] $allowedCodes */
    public static function becauseWrongResponseCodeSet(array $allowedCodes): self
    {
        return new self('Response code does not match specification, allowed are ' . implode(', ', $allowedCodes));
    }
}
