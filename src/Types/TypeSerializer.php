<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Types;

use DateTime;
use DateTimeInterface;

use function base64_encode;
use function Safe\base64_decode;

class TypeSerializer
{
    private const DESERIALIZATION_DATE_FORMAT = 'Y-m-d';
    private const SERIALIZATION_DATE_FORMAT = 'c';


    public static function deserializeDate(string $date, ?DateTimeInterface $dateTimeClass = null): DateTimeInterface
    {
        if ($dateTimeClass !== null) {
            return $dateTimeClass::createFromFormat(self::DESERIALIZATION_DATE_FORMAT, $date);
        }

        return \Safe\DateTime::createFromFormat(self::DESERIALIZATION_DATE_FORMAT, $date);
    }

    public static function serializeDate(DateTime $date): string
    {
        return $date->format(self::SERIALIZATION_DATE_FORMAT);
    }

    public static function deserializeDateTime(string $date, ?DateTimeInterface $dateTimeClass = null): DateTimeInterface
    {
        if ($dateTimeClass !== null) {
            return new $dateTimeClass($date);
        }

        return new \Safe\DateTime($date);
    }

    public static function serializeDateTime(DateTime $date): string
    {
        return $date->format('c');
    }

    public static function deserializeByte(string $data): string
    {
        return base64_decode($data);
    }

    public static function serializeByte(string $data): string
    {
        return base64_encode($data);
    }
}
