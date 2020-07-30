<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Types;

use DateTime;
use OnMoon\OpenApiServerBundle\Types\TypeSerializer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Safe\Exceptions\DatetimeException;
use Throwable;

/**
 * @covers \OnMoon\OpenApiServerBundle\Types\TypeSerializer
 */
class TypeSerializerTest extends TestCase
{
    public function testDeserializeDateReturnsDateTime(): void
    {
        $dateString       = '2020-05-12';
        $expectedDate     = DateTime::createFromFormat('Y-m-d', $dateString);
        $deserializedDate = TypeSerializer::deserializeDate($dateString);
        Assert::assertEquals($expectedDate, $deserializedDate);
    }

    public function testDeserializeDateThrowsException(): void
    {
        $dateString = '22-07-2020';
        $this->expectException(DatetimeException::class);
        TypeSerializer::deserializeDate($dateString);
    }

    public function testSerializeDateReturnsSerializedDate(): void
    {
        $dateString             = '25-12-2020';
        $expectedSerializedDate = '2020-12-25';
        $date                   = new \Safe\DateTime($dateString);
        $serializedDate         = TypeSerializer::serializeDate($date);
        Assert::assertSame($expectedSerializedDate, $serializedDate);
    }

    public function testDeserializeDateTimeReturnsDateTime(): void
    {
        $dateString           = '25-12-2020 12:01:55';
        $expectedDateTime     = new \Safe\DateTime($dateString);
        $deserializedDateTime = TypeSerializer::deserializeDateTime($dateString);
        Assert::assertEquals($expectedDateTime, $deserializedDateTime);
    }

    public function testDeserializeDateTimeThrowsException(): void
    {
        $dateString = 'wrong date string';
        $this->expectException(Throwable::class);
        TypeSerializer::deserializeDateTime($dateString);
    }

    public function testSerializeDateTimeReturnsSerializedDateTime(): void
    {
        $dateString             = '25-12-2020 15:19:21';
        $expectedSerializedDate = '2020-12-25T15:19:21+00:00';
        $date                   = new \Safe\DateTime($dateString);
        $serializedDate         = TypeSerializer::serializeDateTime($date);
        Assert::assertSame($expectedSerializedDate, $serializedDate);
    }

    public function testDeserializeByteReturnsDeserializedString(): void
    {
        $encodedString         = 'VGhpcyBpcyBhbiBlbmNvZGVkIHN0cmluZw==';
        $expectedDecodedString = 'This is an encoded string';
        $decodedString         = TypeSerializer::deserializeByte($encodedString);
        Assert::assertSame($expectedDecodedString, $decodedString);
    }

    public function testSerializeByteReturnsSerializedString(): void
    {
        $decodedString         = 'This is an encoded string';
        $expectedEncodedString = 'VGhpcyBpcyBhbiBlbmNvZGVkIHN0cmluZw==';
        $encodedString         = TypeSerializer::serializeByte($decodedString);
        Assert::assertSame($expectedEncodedString, $encodedString);
    }
}
