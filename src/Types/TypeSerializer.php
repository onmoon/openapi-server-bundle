<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Types;

use DateTime;
use function base64_encode;
use function Safe\base64_decode;

class TypeSerializer
{
    public static function deserializeDate(string $date) : DateTime
    {
        return \Safe\DateTime::createFromFormat('Y-m-d', $date);
    }

    public static function serializeDate(DateTime $date) : string
    {
        return $date->format('Y-m-d');
    }

    public static function deserializeDateTime(string $date) : DateTime
    {
        return new \Safe\DateTime($date);
    }

    public static function serializeDateTime(DateTime $date) : string
    {
        return $date->format('c');
    }

    public static function deserializeByte(string $data) : string
    {
        return base64_decode($data);
    }

    public static function serializeByte(string $data) : string
    {
        return base64_encode($data);
    }
}
