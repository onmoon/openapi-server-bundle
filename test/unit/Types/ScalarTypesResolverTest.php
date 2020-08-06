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

    public function tearDown(): void
    {
        unset($this->scalarTypeResolver);
        parent::tearDown();
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
            'deserialize_string' => [
                'deserialize' => true,
                'id' => 4,
                'value' => '123',
                'expectedValue' => '123',
            ],
            'deserialize_number_php_type_float' => [
                'deserialize' => true,
                'id' => 5,
                'value' => 12,
                'expectedValue' => 12.0,
            ],
            'deserialize_number_php_type_float_format_float' => [
                'deserialize' => true,
                'id' => 6,
                'value' => 12,
                'expectedValue' => 12.0,
            ],
            'deserialize_number_php_type_float_format_double' => [
                'deserialize' => true,
                'id' => 7,
                'value' => 12,
                'expectedValue' => 12.0,
            ],
            'deserialize_number_php_type_int_format_int32' => [
                'deserialize' => true,
                'id' => 8,
                'value' => 12.3,
                'expectedValue' => 12,
            ],
            'deserialize_number_php_type_int_format_int64' => [
                'deserialize' => true,
                'id' => 9,
                'value' => 12.3,
                'expectedValue' => 12,
            ],
            'deserialize_integer' => [
                'deserialize' => true,
                'id' => 10,
                'value' => 12.3,
                'expectedValue' => 12,
            ],
            'deserialize_php_type_bool_empty_string' => [
                'deserialize' => true,
                'id' => 11,
                'value' => '',
                'expectedValue' => false,
            ],
            'deserialize_php_type_bool_true_string' => [
                'deserialize' => true,
                'id' => 11,
                'value' => 'true',
                'expectedValue' => true,
            ],
            'deserialize_php_type_bool_false_boolean' => [
                'deserialize' => true,
                'id' => 11,
                'value' => false,
                'expectedValue' => false,
            ],
            'deserialize_php_type_bool_true_boolean' => [
                'deserialize' => true,
                'id' => 11,
                'value' => true,
                'expectedValue' => true,
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
            'serialize_string' => [
                'deserialize' => false,
                'id' => 4,
                'value' => 'test',
                'expectedValue' => 'test',
            ],
            'serialize_number_php_type_float' => [
                'deserialize' => false,
                'id' => 5,
                'value' => '5.5',
                'expectedValue' => '5.5',
            ],
            'serialize_number_php_type_float_format_float' => [
                'deserialize' => false,
                'id' => 6,
                'value' => '5',
                'expectedValue' => '5',
            ],
            'serialize_number_php_type_float_format_double' => [
                'deserialize' => false,
                'id' => 7,
                'value' => '7E-10',
                'expectedValue' => '7E-10',
            ],
            'serialize_number_php_type_int_format_int32' => [
                'deserialize' => false,
                'id' => 8,
                'value' => 123,
                'expectedValue' => 123,
            ],
            'serialize_number_php_type_int_format_int64' => [
                'deserialize' => false,
                'id' => 9,
                'value' => 123,
                'expectedValue' => 123,
            ],
            'serialize_integer' => [
                'deserialize' => false,
                'id' => 10,
                'value' => 123,
                'expectedValue' => 123,
            ],
            'serialize_php_type_bool_empty_string' => [
                'deserialize' => false,
                'id' => 11,
                'value' => 'false',
                'expectedValue' => 'false',
            ],
            'serialize_php_type_bool_true_string' => [
                'deserialize' => false,
                'id' => 11,
                'value' => 'true',
                'expectedValue' => 'true',
            ],
            'serialize_php_type_bool_false_boolean' => [
                'deserialize' => false,
                'id' => 11,
                'value' => false,
                'expectedValue' => false,
            ],
            'serialize_php_type_bool_true_boolean' => [
                'deserialize' => false,
                'id' => 11,
                'value' => true,
                'expectedValue' => true,
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
        Assert::assertSame($expectedValue, $convertedValue);
    }

    public function testGetTypeWithoutPatternReturnsNull(): void
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

    public function testIsDateTimeReturnsFalseForNonDatetimeType(): void
    {
        $isDateTime = $this->scalarTypeResolver->isDateTime(0);
        Assert::assertFalse($isDateTime);
    }

    public function testIsDateTimeReturnsTrueForDatetimeType(): void
    {
        $isDateTime = $this->scalarTypeResolver->isDateTime(1);
        Assert::assertTrue($isDateTime);
    }

    public function testFindScalarTypeTypeIsNullReturnsStringTypeId(): void
    {
        $scalarType         = $this->scalarTypeResolver->findScalarType(null, '');
        $expectedScalarType = 0;
        Assert::assertSame($expectedScalarType, $scalarType);
    }

    public function testFindScalarTypeFormatNotSetReturnsStringTypeId(): void
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
