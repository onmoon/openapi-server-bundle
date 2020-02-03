<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Naming;

use OnMoon\OpenApiServerBundle\CodeGenerator\ApiServerCodeGenerator;
use function array_map;
use function implode;
use function in_array;
use function lcfirst;
use function rtrim;
use function Safe\preg_match;
use function Safe\preg_replace;
use function str_replace;
use function trim;
use function ucfirst;
use function ucwords;
use const DIRECTORY_SEPARATOR;

class DefaultNamingStrategy implements NamingStrategy
{
    private string $rootNamespace;

    public function __construct(string $rootNamespace)
    {
        $this->rootNamespace = $rootNamespace;
    }

    public function isAllowedPhpPropertyName(string $name) : bool
    {
        return ! preg_match('/^\d/', $name) && preg_match('/^[A-Za-z0-9_]+$/i', $name);
    }

    public function getInterfaceFQCN(string $apiNameSpace, string $operationId) : string
    {
        /** @psalm-var class-string<\OnMoon\OpenApiServerBundle\Interfaces\Service> $interfaceNamespace */
        $interfaceNamespace = $this->buildNamespace(
            $this->rootNamespace,
            ApiServerCodeGenerator::APIS_NAMESPACE,
            $apiNameSpace,
            $this->stringToNamespace($operationId),
            $this->stringToNamespace($operationId) . ApiServerCodeGenerator::SERVICE_SUFFIX,
        );

        return $interfaceNamespace;
    }

    public function stringToNamespace(string $text) : string
    {
        $namespace = $this->padStringThatIsReservedWord(
            $this->padStringStartingWithNumber(
                ucfirst(
                    $this->prepareTextForPhp($text)
                )
            )
        );

        if ($namespace === '') {
            throw CannotCreateNamespace::becauseTextContaintsNoValidSymbols($text);
        }

        return $namespace;
    }

    public function stringToMethodName(string $text) : string
    {
        $propertyName = $this->padStringThatIsReservedWord(
            $this->padStringStartingWithNumber(
                lcfirst(
                    $this->prepareTextForPhp($text)
                )
            )
        );

        if ($propertyName === '') {
            throw CannotCreatePropertyName::becauseTextContaintsNoValidSymbols($text);
        }

        return $propertyName;
    }

    private function prepareTextForPhp(string $text) : string
    {
        /** @var string $filteredText */
        $filteredText = preg_replace('/[^\w]/', ' ', $text);

        return str_replace(' ', '', ucwords($filteredText));
    }

    public function isPhpReservedWord(string $text) : bool
    {
        return in_array($text, PhpReservedWords::LIST);
    }

    public function buildNamespace(string ...$parts) : string
    {
        return implode('\\', array_map(static fn(string $part) : string => trim($part, '\\'), $parts));
    }

    public function buildPath(string ...$parts) : string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            array_map(static fn(string $part) : string => rtrim($part, DIRECTORY_SEPARATOR), $parts)
        );
    }

    private function padStringThatIsReservedWord(string $string) : string
    {
        return $this->isPhpReservedWord($string) ? '_' . $string : $string;
    }

    private function padStringStartingWithNumber(string $string) : string
    {
        return preg_match('/^\d/', $string) ? '_' . $string : $string;
    }
}
