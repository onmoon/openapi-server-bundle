<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Exception;

use OnMoon\OpenApiServerBundle\Exception\ApiCallFailed;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\Exception\ApiCallFailed
 */
final class ApiCallFailedTest extends TestCase
{
    public function testApiCallFailedExceptionThrowable(): void
    {
        $this->expectException(ApiCallFailed::class);

        $apiCallFailedException = ApiCallFailed::becauseApiLoaderNotFound();

        throw new $apiCallFailedException();
    }

    public function testApiCallFailedBecauseNotImplementedPrintsInterface(): void
    {
        $this->expectException(ApiCallFailed::class);

        $apiCallFailedException = ApiCallFailed::becauseNotImplemented('testInterface');

        throw new $apiCallFailedException();
    }
}
