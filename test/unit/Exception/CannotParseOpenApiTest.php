<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Exception;

use OnMoon\OpenApiServerBundle\Exception\CannotParseOpenApi;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\Exception\CannotParseOpenApi
 */
final class CannotParseOpenApiTest extends TestCase
{
    public function testCannotParseOpenApi(): void
    {
        $cannotParseOpenApiException = CannotParseOpenApi::becauseNoOperationIdSpecified([
            'method' => 'testMethod',
            'url' => 'testUrl',
            'path' => 'testPath',
        ]);

        $this->expectException(CannotParseOpenApi::class);

        throw new $cannotParseOpenApiException();
    }
}
