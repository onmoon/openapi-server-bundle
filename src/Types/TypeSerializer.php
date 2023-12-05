<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Types;

use DateTime;
use DateTimeInterface;
use Exception;
use Safe\Exceptions\DatetimeException;

use function base64_encode;
use function error_get_last;
use function method_exists;
use function Safe\base64_decode;
use function sprintf;

class TypeSerializer
{
    private const DATE_FORMAT     = 'Y-m-d';
    private const DATETIME_FORMAT = 'c';

    /**
     * @psalm-param class-string<T> $dateTimeClass
     *
     * @template T of DateTimeInterface
     */
    public static function deserializeDate(string $date, ?string $dateTimeClass = null): DateTimeInterface
    {
        if ($dateTimeClass === null) {
            return \Safe\DateTime::createFromFormat(self::DATE_FORMAT, $date);
        }

        if (method_exists($dateTimeClass, 'createFromFormat') === false) {
            throw new Exception(sprintf(
                'Method createFromFormat does not exist in class %s',
                $dateTimeClass
            ));
        }

        /** @psalm-suppress UndefinedMethod */
        $deserializedDate = $dateTimeClass::createFromFormat(self::DATE_FORMAT, $date);

        if ($deserializedDate === false) {
            $error = error_get_last();

            throw new DatetimeException($error['message'] ?? 'An error occurred');
        }

        return $deserializedDate;
    }

    public static function serializeDate(DateTime $date): string
    {
        return $date->format(self::DATE_FORMAT);
    }

    /**
     * @psalm-param class-string<T> $dateTimeClass
     *
     * @template T of DateTimeInterface
     */
    public static function deserializeDateTime(string $date, ?string $dateTimeClass = null): DateTimeInterface
    {
        if ($dateTimeClass === null) {
            return new \Safe\DateTime($date);
        }

        /** @psalm-suppress InvalidStringClass */
        return new $dateTimeClass($date);
    }

    public static function serializeDateTime(DateTime $date): string
    {
        return $date->format(self::DATETIME_FORMAT);
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
