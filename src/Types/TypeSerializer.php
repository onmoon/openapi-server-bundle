<?php


namespace OnMoon\OpenApiServerBundle\Types;


class TypeSerializer
{
    public static function DeserializeDate(string $date) : \DateTime {
        return \Safe\DateTime::createFromFormat('Y-m-d', $date);
    }

    public static function SerializeDate(\DateTime $date) : string {
        return $date->format('Y-m-d');
    }

    public static function DeserializeDateTime(string $date) : \DateTime {
        return new \Safe\DateTime($date);
    }

    public static function SerializeDateTime(\DateTime $date) : string {
        return $date->format('c');
    }

    public static function DeserializeByte(string $data) : string {
        return \Safe\base64_decode($data);
    }

    public static function SerializeByte(string $data) : string {
        return base64_encode($data);
    }

}
