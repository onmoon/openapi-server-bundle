<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Mapper;

use ArrayAccess;
use ArrayObject;
use BadMethodCallException;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\PhpParserDtoFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Factory\OperationDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Exception\CannotMapToDto;
use ReflectionClass;
use ReflectionException;
use ReflectionType;
use Safe\Exceptions\StringsException;
use function array_key_exists;
use function array_map;
use function class_exists;
use function count;
use function get_class;
use function get_class_methods;
use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function method_exists;
use function preg_quote;
use function Safe\preg_match;
use function Safe\settype;
use function Safe\sort;
use function Safe\substr;
use function strlen;
use function strpos;
use function strtr;

/** phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingTraversableTypeHintSpecification */
class DtoMapper
{
    private NamingStrategy $namingStrategy;

    public function __construct(NamingStrategy $namingStrategy)
    {
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * @param array|object $from
     *
     * @throws CannotMapToDto
     * @throws NotAnArrayValue
     * @throws UnexpectedNullValue
     * @throws UnexpectedScalarValue
     * @throws ReflectionException
     * @throws StringsException
     */
    public function map($from, string $toDTO, int $httpCode = 0, ?callable $propertyMapper = null) : object
    {
        if (! class_exists($toDTO)) {
            throw CannotMapToDto::becauseRootClassDoesNotExist($toDTO);
        }

        $reflectionClass = new ReflectionClass($toDTO);
        $instance        = $reflectionClass->newInstanceWithoutConstructor();

        $properties = $reflectionClass->getProperties();
        foreach ($properties as $property) {
            if ($property->getName() === PhpParserDtoFactory::RESPONSE_CODE_VARIABLE_NAME) {
                $property->setAccessible(true);
                $property->setValue($instance, $httpCode);
                continue;
            }

            $nextMapper = null;
            if ($propertyMapper !== null) {
                /** @var mixed $mappedProperty */
                $mappedProperty = $propertyMapper($property->getName(), []);
                if (! is_string($mappedProperty) || empty($mappedProperty)) {
                    throw new MapperReturnedNotAString($property->getName(), $toDTO, $mappedProperty);
                }

                /** @psalm-suppress MissingClosureReturnType */
                $nextMapper = static function (string $name, array $context) use ($mappedProperty, $propertyMapper) {
                    $context[] = $mappedProperty;

                    return $propertyMapper($name, $context);
                };
            } else {
                $mappedProperty = $property->getName();
            }

            /** @var mixed|null $rawValue */
            $rawValue = $this->getValue($from, $mappedProperty);
            /** @var ReflectionType $type */
            $type = $property->getType();

            if ($rawValue === null) {
                if ($type->allowsNull()) {
                    continue;
                }

                throw new UnexpectedNullValue($property->getName(), $toDTO, $from);
            }

            $typeName = (string) $type;
            if ($typeName === 'array') {
                if (! (is_array($rawValue) || $rawValue instanceof ArrayObject)) {
                    throw new NotAnArrayValue($property->getName(), $toDTO, $rawValue);
                }

                /** @var mixed[] $value */
                $value = [];
                if (count($rawValue) !== 0) {
                    $phpDoc = $property->getDocComment();
                    if (! $phpDoc) {
                        throw CannotMapToDto::becausePhpDocIsCorrupt($property->getName(), $toDTO);
                    }

                    $regExp = '#@var\s+([^[\s]+)\\[\\](|\\|null)\s+\\$' . preg_quote($property->getName(), '#') . '#';
                    if (! preg_match($regExp, $phpDoc, $match) || $match === null) {
                        throw CannotMapToDto::becausePhpDocIsCorrupt($property->getName(), $toDTO);
                    }

                    /** @var string $shortClassName */
                    $shortClassName = $match[1];

                    if (preg_match('#' . preg_quote(OperationDefinitionFactory::DTO_SUFFIX) . '$#', $shortClassName)) {
                        $parentNamespace = $reflectionClass->getNamespaceName();

                        $fullClass = $this->namingStrategy->buildNamespace(
                            $parentNamespace,
                            substr($shortClassName, 0, -strlen(OperationDefinitionFactory::DTO_SUFFIX)),
                            $shortClassName
                        );

                        /** @var mixed $item */
                        foreach ($rawValue as $item) {
                            if (! is_array($item) && ! is_object($item)) {
                                throw new UnexpectedScalarValue($property->getName(), $toDTO, $item);
                            }

                            if (! class_exists($fullClass)) {
                                throw CannotMapToDto::becauseClassDoesNotExist(
                                    $fullClass,
                                    $property->getName(),
                                    $toDTO
                                );
                            }

                            $value[] = $this->map($item, $fullClass, 0, $nextMapper);
                        }
                    } else {
                        /** @var mixed $item */
                        foreach ($rawValue as $item) {
                            /** phpcs:disable Generic.PHP.ForbiddenFunctions.Found */
                            settype($item, $shortClassName);
                            /** @psalm-suppress MixedAssignment */
                            $value[] = $item;
                        }
                    }
                }
            } elseif ($type->isBuiltin()) {
                /** phpcs:disable Generic.PHP.ForbiddenFunctions.Found */
                settype($rawValue, $typeName);
                /** @var mixed $value */
                $value = $rawValue;
            } elseif (class_exists($typeName)) {
                if (! is_array($rawValue) && ! is_object($rawValue)) {
                    throw new UnexpectedScalarValue($property->getName(), $toDTO, $rawValue);
                }

                /** @var mixed $value */
                $value = $this->map($rawValue, $typeName, 0, $nextMapper);
            } else {
                throw CannotMapToDto::becauseUnknownType($typeName, $property->getName(), $toDTO);
            }

            $property->setAccessible(true);
            $property->setValue($instance, $value);
        }

        return $instance;
    }

    /**
     * @param array|object $object
     *
     * @return mixed|null
     *
     * @throws StringsException
     */
    private function getValue($object, string $item)
    {
        if (((is_array($object) || $object instanceof ArrayObject) &&
                (isset($object[$item]) || array_key_exists($item, (array) $object)))
            || ($object instanceof ArrayAccess && isset($object[$item]))
        ) {
            return $object[$item];
        }

        if (! is_object($object)) {
            return null;
        }

        if (isset($object->$item) || array_key_exists((string) $item, (array) $object)) {
            return $object->$item;
        }

        /** @var array[] $cache */
        static $cache = [];

        $class = get_class($object);

        // object method
        // precedence: getXxx() > isXxx() > hasXxx()
        if (! isset($cache[$class])) {
            $methods = get_class_methods($object);
            sort($methods);
            $lcMethods = array_map(
                static function (string $value) {
                    return self::strToLowerEn($value);
                },
                $methods
            );
            /** @var string[] $classCache */
            $classCache = [];
            /**
             * @var int $i
             * @var string $method */
            foreach ($methods as $i => $method) {
                $classCache[$method] = $method;
                $classCache[$lcName  = $lcMethods[$i]] = $method;

                if ($lcName[0] === 'g' && strpos($lcName, 'get') === 0) {
                    $name   = substr($method, 3);
                    $lcName = substr($lcName, 3);
                } elseif ($lcName[0] === 'i' && strpos($lcName, 'is') === 0) {
                    $name   = substr($method, 2);
                    $lcName = substr($lcName, 2);
                } elseif ($lcName[0] === 'h' && strpos($lcName, 'has') === 0) {
                    $name   = substr($method, 3);
                    $lcName = substr($lcName, 3);
                    if (in_array('is' . $lcName, $lcMethods)) {
                        continue;
                    }
                } else {
                    continue;
                }

                // skip get() and is() methods (in which case, $name is empty)
                if (! $name) {
                    continue;
                }

                if (! isset($classCache[$name])) {
                    $classCache[$name] = $method;
                }

                if (isset($classCache[$lcName])) {
                    continue;
                }

                $classCache[$lcName] = $method;
            }

            $cache[$class] = $classCache;
        }

        $magicCall = false;
        $lcItem    = $this->strToLowerEn($item);
        if (isset($cache[$class][$item])) {
            /** @var string $method */
            $method = $cache[$class][$item];
        } elseif (isset($cache[$class][$lcItem])) {
            /** @var string $method */
            $method = $cache[$class][$lcItem];
        } elseif (isset($cache[$class]['__call'])) {
            /** @var string $method */
            $method    = $item;
            $magicCall = true;
        } else {
            return null;
        }

        if (! method_exists($object, $method)) {
            return null;
        }

        try {
            /** @var mixed $ret */
            $ret = $object->$method();
        } catch (BadMethodCallException $e) {
            if ($magicCall) {
                return null;
            }

            throw $e;
        }

        return $ret;
    }

    private static function strToLowerEn(string $str) : string
    {
        return strtr($str, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
    }
}
