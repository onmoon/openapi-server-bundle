<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Exception;

use OnMoon\OpenApiServerBundle\Exception\OpenApiError;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\Exception\OpenApiError
 */
final class OpenApiErrorTest extends TestCase
{
    public function testOpenApiErrorExceptionThrowable(): void
    {
        $this->expectException(OpenApiError::class);
        $this->expectExceptionMessage('testMessage');

        throw new OpenApiError('testMessage');
    }
}
