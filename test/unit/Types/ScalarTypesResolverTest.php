<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Types;

use cebe\openapi\spec\Type;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Safe\DateTime;
use Safe\Exceptions\DatetimeException;

/**
 * @covers  \OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver
 */
class ScalarTypesResolverTest extends TestCase
{
    private ScalarTypesResolver $scalarTypeResolver;

    protected function setUp(): void
    {
        $this->scalarTypeResolver = new ScalarTypesResolver();
    }

    /**
     * @return array|mixed[]
     *
     * @throws DatetimeException
     */
    public function convertDeserializeDataProvider(): array
    {
        return [
            'deserialize_null' => [
                'deserialize' => true,
                'id' => 1,
                'value' => null,
                'expectedValue' => null,
            ],
            'deserialize_date' => [
                'deserialize' => true,
                'id' => 1,
                'value' => '2020-07-31',
                'expectedValue' => DateTime::createFromFormat('Y-m-d', '2020-07-31'),
            ],
            'deserialize_date_time' => [
                'deserialize' => true,
                'id' => 2,
                'value' => '2020-07-31 15:35:11',
                'expectedValue' => new DateTime('2020-07-31 15:35:11'),
            ],
            'deserialize_byte' => [
                'deserialize' => true,
                'id' => 3,
                'value' => 'VGhpcyBpcyBhbiBlbmNvZGVkIHN0cmluZw==',
                'expectedValue' => 'This is an encoded string',
            ],
            'deserialize_php_type_bool' => [
                'deserialize' => true,
                'id' => 11,
                'value' => '',
                'expectedValue' => false,
            ],
            'deserialize_php_type_float' => [
                'deserialize' => true,
                'id' => 5,
                'value' => '1.1qwerty',
                'expectedValue' => 1.1,
            ],
            'deserialize_php_type_int' => [
                'deserialize' => true,
                'id' => 10,
                'value' => 'q1werty',
                'expectedValue' => 0,
            ],
        ];
    }

    /**
     * @param mixed $value
     * @param mixed $expectedValue
     *
     * @dataProvider convertDeserializeDataProvider
     */
    public function testConvertDeserializesValue(bool $deserialize, int $id, $value, $expectedValue): void
    {
        $convertedValue = $this->scalarTypeResolver->convert($deserialize, $id, $value);
        Assert::assertEquals($expectedValue, $convertedValue);
    }

    /**
     * @return array|mixed[]
     *
     * @throws DatetimeException
     */
    public function convertSerializeDataProvider(): array
    {
        return [
            'serialize_null' => [
                'deserialize' => false,
                'id' => 1,
                'value' => null,
                'expectedValue' => null,
            ],
            'serialize_date' => [
                'deserialize' => false,
                'id' => 1,
                'value' => DateTime::createFromFormat('Y-m-d', '2020-07-31'),
                'expectedValue' => '2020-07-31',
            ],
            'serialize_date_time' => [
                'deserialize' => false,
                'id' => 2,
                'value' => new DateTime('2020-07-31 15:35:11'),
                'expectedValue' => '2020-07-31T15:35:11+00:00',
            ],
            'serialize_byte' => [
                'deserialize' => false,
                'id' => 3,
                'value' => 'This is an encoded string',
                'expectedValue' => 'VGhpcyBpcyBhbiBlbmNvZGVkIHN0cmluZw==',
            ],
            'serialize_value_without_serializer_function' => [
                'deserialize' => false,
                'id' => 11,
                'value' => 'q1werty',
                'expectedValue' => 'q1werty',
            ],
        ];
    }

    /**
     * @param mixed $value
     * @param mixed $expectedValue
     *
     * @dataProvider convertSerializeDataProvider
     */
    public function testConvertSerializesValue(bool $deserialize, int $id, $value, $expectedValue): void
    {
        $convertedValue = $this->scalarTypeResolver->convert($deserialize, $id, $value);
        Assert::assertEquals($expectedValue, $convertedValue);
    }

    public function testGetPatternReturnsNull(): void
    {
        $pattern = $this->scalarTypeResolver->getPattern(0);
        Assert::assertNull($pattern);
    }

    public function testGetPatternReturnsPattern(): void
    {
        $pattern         = $this->scalarTypeResolver->getPattern(1);
        $expectedPattern = '\d{4}-\d{2}-\d{2}';
        Assert::assertSame($expectedPattern, $pattern);
    }

    public function testGetPhpTypeReturnsPhpType(): void
    {
        $phpType         = $this->scalarTypeResolver->getPhpType(0);
        $expectedPhpType = 'string';
        Assert::assertSame($expectedPhpType, $phpType);
    }

    public function testIsDateTimeReturnsTrue(): void
    {
        $isDateTime = $this->scalarTypeResolver->isDateTime(0);
        Assert::assertFalse($isDateTime);
    }

    public function testIsDateTimeReturnsFalse(): void
    {
        $isDateTime = $this->scalarTypeResolver->isDateTime(1);
        Assert::assertTrue($isDateTime);
    }

    public function testFindScalarTypeTypeIsNullReturnsZero(): void
    {
        $scalarType         = $this->scalarTypeResolver->findScalarType(null, '');
        $expectedScalarType = 0;
        Assert::assertSame($expectedScalarType, $scalarType);
    }

    public function testFindScalarTypeFormatNotSetReturnsZero(): void
    {
        $scalarType         = $this->scalarTypeResolver->findScalarType(Type::STRING, '');
        $expectedScalarType = 0;
        Assert::assertSame($expectedScalarType, $scalarType);
    }

    public function testFindScalarTypeReturnsDefaultZero(): void
    {
        $scalarType         = $this->scalarTypeResolver->findScalarType(Type::STRING, 'test');
        $expectedScalarType = 0;
        Assert::assertSame($expectedScalarType, $scalarType);
    }

    public function testFindScalarTypeReturnsType(): void
    {
        $scalarType         = $this->scalarTypeResolver->findScalarType(Type::STRING, 'date');
        $expectedScalarType = 1;
        Assert::assertSame($expectedScalarType, $scalarType);
    }

    public function testFindScalarTypeFormatNotSetReturnsType(): void
    {
        $scalarType         = $this->scalarTypeResolver->findScalarType(Type::NUMBER, '');
        $expectedScalarType = 5;
        Assert::assertSame($expectedScalarType, $scalarType);
    }
}
