<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Mapper;

use OnMoon\OpenApiServerBundle\Exception\OpenApiError;
use Safe\Exceptions\StringsException;
use function Safe\sprintf;

class MapperReturnedNotAString extends OpenApiError
{
    /** @var mixed $value */
    private $value;

    /**
     * @param mixed $value
     *
     * @throws StringsException
     */
    public function __construct(string $name, string $class, $value)
    {
        $message     = sprintf(
            'Property name is not a string for "%s" in "%s"',
            $name,
            $class
        );
        $this->value = $value;
        parent::__construct($message);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
