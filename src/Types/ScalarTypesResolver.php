<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Types;

use cebe\openapi\spec\Type;

use function is_string;
use function Safe\settype;

final class ScalarTypesResolver
{
    /**
     * @psalm-var list<array{
     *     type:string,
     *     phpType:string,
     *     format?:string,
     *     pattern?:string,
     *     serializer?:string,
     *     deserializer?:string
     * }>
     * @var mixed[]
     */
    private array $scalarTypes         = [];
    public const SERIALIZER_FULL_CLASS = TypeSerializer::class;
    public const SERIALIZER_CLASS      = 'TypeSerializer';

    public function __construct()
    {
        $this->scalarTypes = [
            ['type' => Type::STRING, 'phpType' => 'string'],
            [
                'type' => Type::STRING,
                'format' => 'date',
                'phpType' => '\DateTime',
                'pattern' => '\d{4}-\d{2}-\d{2}',
                'deserializer' => 'deserializeDate',
                'serializer' => 'serializeDate',
            ],
            [
                'type' => Type::STRING,
                'format' => 'date-time',
                'phpType' => '\DateTime',
                'pattern' => '\d{4}-\d{2}-\d{2}( |T)\d{2}:\d{2}:\d{2}(|\.\d*)(Z|(\+|-)\d{2}:\d{2})',
                'deserializer' => 'deserializeDateTime',
                'serializer' => 'serializeDateTime',
            ],
            [
                'type' => Type::STRING,
                'format' => 'byte',
                'phpType' => 'string',
                'pattern' => '[a-zA-Z0-9+/]+[=]*',
                'deserializer' => 'deserializeByte',
                'serializer' => 'serializeByte',
            ],
            ['type' => Type::STRING, 'format' => 'binary', 'phpType' => 'string'],

            ['type' => Type::NUMBER, 'phpType' => 'float', 'pattern' => '[0-9.]+'],
            ['type' => Type::NUMBER, 'format' => 'float', 'phpType' => 'float', 'pattern' => '[0-9.]+'],
            ['type' => Type::NUMBER, 'format' => 'double', 'phpType' => 'float', 'pattern' => '[0-9.]+'],
            ['type' => Type::NUMBER, 'format' => 'int32', 'phpType' => 'int', 'pattern' => '\d+'],
            ['type' => Type::NUMBER, 'format' => 'int64', 'phpType' => 'int', 'pattern' => '\d+'],
            ['type' => Type::INTEGER, 'phpType' => 'int', 'pattern' => '\\d+'],
            ['type' => Type::BOOLEAN, 'phpType' => 'bool', 'pattern' => 'true|false'],
        ];
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function convert(bool $deserialize, int $id, $value)
    {
        if ($value === null) {
            return null;
        }

        $format = $this->scalarTypes[$id];

        if ($deserialize && isset($format['deserializer'])) {
            return TypeSerializer::{$format['deserializer']}($value);
        }

        if (! $deserialize && isset($format['serializer'])) {
            return TypeSerializer::{$format['serializer']}($value);
        }

        if ($deserialize) {
            if (is_string($value) && $format['phpType'] === 'bool') {
                $value = $value === 'true';
            } else {
                /** phpcs:disable Generic.PHP.ForbiddenFunctions.Found */
                settype($value, $format['phpType']);
            }
        }

        return $value;
    }

    public function getPattern(int $id): ?string
    {
        $format = $this->scalarTypes[$id];

        if (isset($format['pattern'])) {
            return $format['pattern'];
        }

        return null;
    }

    public function getPhpType(int $id): string
    {
        return $this->scalarTypes[$id]['phpType'];
    }

    public function isDateTime(int $id): bool
    {
        return $this->getPhpType($id) === '\DateTime';
    }

    public function findScalarType(?string $type, ?string $format): int
    {
        if ($type === null) {
            return 0;
        }

        if ($format !== null) {
            foreach ($this->scalarTypes as $id => $scalar) {
                if (
                    $scalar['type'] === $type &&
                    isset($scalar['format']) &&
                    $scalar['format'] === $format
                ) {
                    return $id;
                }
            }
        }

        foreach ($this->scalarTypes as $id => $scalar) {
            if (
                $scalar['type'] === $type &&
                ! isset($scalar['format'])
            ) {
                return $id;
            }
        }

        return 0;
    }
}
