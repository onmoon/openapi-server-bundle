<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\OpenApi;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use DateTime;
use function Safe\base64_decode;
use function settype;

class ScalarTypesResolver
{
    /**
     * @psalm-var list<array{
     *     type:string,
     *     phpType:string,
     *     format?:string,
     *     pattern?:string,
     *     serializer?:\Closure
     * }>
     * @var mixed[]
     */
    private array $scalarTypes = [];

    public function __construct()
    {
        $this->scalarTypes = [
            ['type' => Type::STRING, 'phpType' => 'string'],
            [
                'type' => Type::STRING,
                'format' => 'date',
                'phpType' => '\DateTime',
                'pattern' => '\d{4}-\d{2}-\d{2}',
                'serializer' => static fn(string $a) : DateTime => DateTime::createFromFormat('Y-m-d', $a),
            ],
            [
                'type' => Type::STRING,
                'format' => 'date-time',
                'phpType' => '\DateTime',
                'pattern' => '\d{4}-\d{2}-\d{2}( |T)\d{2}:\d{2}:\d{2}(|\.\d*)(Z|(\+|-)\d{2}:\d{2})',
                'serializer' => static fn(string $a) : DateTime => new DateTime($a),
            ],
            [
                'type' => Type::STRING,
                'format' => 'byte',
                'phpType' => 'string',
                'pattern' => '[a-zA-Z0-9+/]+[=]*',
                'serializer' => static fn(string $a) : string => base64_decode($a),
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
     * @return bool|string
     */
    public function getPattern(int $id)
    {
        $format = $this->scalarTypes[$id];

        if (isset($format['pattern'])) {
            return $format['pattern'];
        }

        return false;
    }

    public function getPhpType(int $id) : string
    {
        return (string) $this->scalarTypes[$id]['phpType'];
    }

    /**
     * @return mixed
     */
    public function serialize(int $id, string $value)
    {
        $format = $this->scalarTypes[$id];

        if (isset($format['serializer'])) {
            return $format['serializer']($value);
        }

        /** phpcs:disable Generic.PHP.ForbiddenFunctions.Found */
        settype($value, $format['phpType']);

        return $value;
    }

    public function findScalarType(Schema $schema) : int
    {
        if (empty($schema->type)) {
            return 0;
        }

        if (! empty($schema->format)) {
            foreach ($this->scalarTypes as $id => $scalar) {
                if ($scalar['type'] === $schema->type &&
                    isset($scalar['format']) &&
                    $scalar['format'] === $schema->format
                ) {
                    return $id;
                }
            }
        }

        foreach ($this->scalarTypes as $id => $scalar) {
            if ($scalar['type'] === $schema->type &&
                ! isset($scalar['format'])
            ) {
                return $id;
            }
        }

        return 0;
    }
}
