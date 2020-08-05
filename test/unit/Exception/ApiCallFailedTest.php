<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Exception;

use OnMoon\OpenApiServerBundle\Exception\ApiCallFailed;
use PHPUnit\Framework\TestCase;

use function Safe\sprintf;

/**
 * @covers \OnMoon\OpenApiServerBundle\Exception\ApiCallFailed
 */
final class ApiCallFailedTest extends TestCase
{
    public function testBecauseApiLoaderNotFoundPrintsCorrectMessage(): void
    {
        $expectedExceptionMessage = 'ApiLoader not found. Try re-generating code';

        $this->expectException(ApiCallFailed::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        throw ApiCallFailed::becauseApiLoaderNotFound();
    }

    public function testApiCallFailedBecauseNotImplementedPrintsInterface(): void
    {
        $interface                = 'TestInterfaceName';
        $expectedExceptionMessage = sprintf(
            'Api call implementation not found. Please implement "%s" interface',
            $interface
        );

        $this->expectException(ApiCallFailed::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        throw ApiCallFailed::becauseNotImplemented($interface);
    }
}
