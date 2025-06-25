<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Exception;

use OnMoon\OpenApiServerBundle\Exception\CannotParseOpenApi;
use PHPUnit\Framework\TestCase;

use function sprintf;

/** @covers \OnMoon\OpenApiServerBundle\Exception\CannotParseOpenApi */
final class CannotParseOpenApiTest extends TestCase
{
    public function testBecauseNoOperationIdSpecifiedShowsCorrectErrorMessage(): void
    {
        $context = [
            'method' => 'testMethod',
            'url' => 'testUrl',
            'path' => 'testPath',
        ];

        $exceptionMessage = sprintf(
            'No operationId specified for operation: "%s" of path: "%s" in specification file: "%s".',
            $context['method'],
            $context['url'],
            $context['path']
        );

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage($exceptionMessage);

        throw CannotParseOpenApi::becauseNoOperationIdSpecified($context);
    }

    public function testBecauseDuplicateOperationIdShowsCorrectErrorMessage(): void
    {
        $id = '512';

        $context = [
            'method' => 'testMethod',
            'url' => 'testUrl',
            'path' => 'testPath',
        ];

        $exceptionMessage = sprintf(
            'Operation ID "%s" already taken for operation: "%s" of path: "%s" in specification file: "%s".',
            $id,
            $context['method'],
            $context['url'],
            $context['path']
        );

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage($exceptionMessage);

        throw CannotParseOpenApi::becauseDuplicateOperationId($id, $context);
    }

    public function testBecauseRootIsNotObjectShowsCorrectErrorMessage(): void
    {
        $context = [
            'location' => 'http://example.com',
            'method' => 'testMethod',
            'url' => 'testUrl',
            'path' => 'testPath',
        ];

        $moreInfo = '(array as root is insecure, see https://haacked.com/archive/2009/06/25/json-hijacking.aspx/) ';

        $exceptionMessage = sprintf(
            'Only object is allowed as root in %s ' . $moreInfo .
            'for operation: "%s" of path: "%s" in specification file: "%s".',
            $context['location'],
            $context['method'],
            $context['url'],
            $context['path']
        );

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage($exceptionMessage);

        throw CannotParseOpenApi::becauseRootIsNotObject($context, true);
    }

    public function testBecauseOnlyScalarAreAllowedShowsCorrectErrorMessage(): void
    {
        $propertyName = 'someRandomName';

        $context = [
            'location' => 'http://example.com',
            'method' => 'testMethod',
            'url' => 'testUrl',
            'path' => 'testPath',
        ];

        $exceptionMessage = sprintf(
            'Cannot generate property for DTO class, property "%s" is not scalar in %s for operation: "%s" of path: "%s" in specification file: "%s".',
            $propertyName,
            $context['location'],
            $context['method'],
            $context['url'],
            $context['path']
        );

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage($exceptionMessage);

        throw CannotParseOpenApi::becauseOnlyScalarAreAllowed($propertyName, $context);
    }

    public function testBecauseOpenapi31TypesNotSupportedShowsCorrectErrorMessage(): void
    {
        $propertyName = 'someRandomName';

        $context = [
            'location' => 'http://example.com',
            'method' => 'testMethod',
            'url' => 'testUrl',
            'path' => 'testPath',
        ];

        $exceptionMessage = sprintf(
            'Cannot generate property for DTO class, property "%s" has multiple types in %s for operation: "%s" of path: "%s" in specification file: "%s".',
            $propertyName,
            $context['location'],
            $context['method'],
            $context['url'],
            $context['path']
        );

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage($exceptionMessage);

        throw CannotParseOpenApi::becauseOpenapi31TypesNotSupported($propertyName, $context);
    }

    public function testBecauseArrayIsNotDescribedShowsCorrectErrorMessage(): void
    {
        $propertyName = 'someRandomName';

        $context = [
            'location' => 'http://example.com',
            'method' => 'testMethod',
            'url' => 'testUrl',
            'path' => 'testPath',
        ];

        $exceptionMessage = sprintf(
            'Cannot generate property for DTO class, property "%s" is array without items description in %s for operation: "%s" of path: "%s" in specification file: "%s".',
            $propertyName,
            $context['location'],
            $context['method'],
            $context['url'],
            $context['path']
        );

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage($exceptionMessage);

        throw CannotParseOpenApi::becauseArrayIsNotDescribed($propertyName, $context);
    }

    public function testBecauseTypeNotSupportedShowsCorrectErrorMessage(): void
    {
        $propertyName = 'someRandomName';
        $type         = 'someRandomType';

        $context = [
            'location' => 'http://example.com',
            'method' => 'testMethod',
            'url' => 'testUrl',
            'path' => 'testPath',
        ];

        $exceptionMessage = sprintf(
            'Cannot generate property for DTO class, property "%s" type "%s" is not supported in %s for operation: "%s" of path: "%s" in specification file: "%s".',
            $propertyName,
            $type,
            $context['location'],
            $context['method'],
            $context['url'],
            $context['path']
        );

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage($exceptionMessage);

        throw CannotParseOpenApi::becauseTypeNotSupported($propertyName, $type, $context);
    }
}
