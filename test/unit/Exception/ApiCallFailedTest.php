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
    public function testApiCallFailedBecauseNotImplementedPrintsInterface(): void
    {
        $this->expectException(ApiCallFailed::class);
        $this->expectExceptionMessage('testInterfaceName');

        throw ApiCallFailed::becauseNotImplemented('testInterfaceName');
    }
}
