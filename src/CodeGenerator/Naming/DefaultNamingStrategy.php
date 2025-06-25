<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Naming;

use OnMoon\OpenApiServerBundle\CodeGenerator\NameGenerator;
// phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use sspat\ReservedWords\ReservedWords;

use function array_map;
use function implode;
use function lcfirst;
use function rtrim;
use function Safe\preg_match;
use function Safe\preg_replace;
use function str_replace;
use function trim;
use function ucwords;

use const DIRECTORY_SEPARATOR;

final class DefaultNamingStrategy implements NamingStrategy
{
    private ReservedWords $reservedWords;
    private string $rootNamespace;
    private string $languageLevel;

    public function __construct(
        ReservedWords $reservedWords,
        string $rootNamespace,
        string $languageLevel
    ) {
        $this->reservedWords = $reservedWords;
        $this->rootNamespace = $rootNamespace;
        $this->languageLevel = $languageLevel;
    }

    public function isAllowedPhpPropertyName(string $name): bool
    {
        return preg_match('/^\d/', $name) === 0 && preg_match('/^[A-Za-z0-9_]+$/', $name) === 1;
    }

    public function getInterfaceFQCN(string $apiNameSpace, string $operationId): string
    {
        /** @psalm-var class-string<RequestHandler> $interfaceNamespace */
        $interfaceNamespace = $this->buildNamespace(
            $this->rootNamespace,
            NameGenerator::APIS_NAMESPACE,
            $apiNameSpace,
            $this->stringToNamespace($operationId),
            $this->stringToNamespace($operationId),
        );

        return $interfaceNamespace;
    }

    public function stringToNamespace(string $text): string
    {
        $namespace = $this->padStringThatIsReservedNamespaceName(
            $this->padStringStartingWithNumber(
                $this->prepareTextForPhp($text)
            )
        );

        if ($namespace === '') {
            throw CannotCreateNamespace::becauseTextContainsNoValidSymbols($text);
        }

        return $namespace;
    }

    public function stringToMethodName(string $text): string
    {
        $propertyName = $this->padStringThatIsReservedMethodName(
            $this->padStringStartingWithNumber(
                lcfirst(
                    $this->prepareTextForPhp($text)
                )
            )
        );

        if ($propertyName === '') {
            throw CannotCreatePropertyName::becauseTextContainsNoValidSymbols($text);
        }

        return $propertyName;
    }

    public function buildNamespace(string ...$parts): string
    {
        return implode('\\', array_map(static fn (string $part): string => trim($part, '\\'), $parts));
    }

    public function buildPath(string ...$parts): string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            array_map(static fn (string $part): string => rtrim($part, DIRECTORY_SEPARATOR), $parts)
        );
    }

    private function prepareTextForPhp(string $text): string
    {
        /** @var string $filteredText */
        $filteredText = preg_replace('/[^A-Z0-9]/i', ' ', $text);

        return str_replace(' ', '', ucwords($filteredText));
    }

    private function padStringThatIsReservedNamespaceName(string $string): string
    {
        return $this->reservedWords->isReservedNamespaceName($string, $this->languageLevel) ? '_' . $string : $string;
    }

    private function padStringThatIsReservedMethodName(string $string): string
    {
        return $this->reservedWords->isReservedMethodName($string, $this->languageLevel) ? '_' . $string : $string;
    }

    private function padStringStartingWithNumber(string $string): string
    {
        return preg_match('/^\d/', $string) === 1 ? '_' . $string : $string;
    }
}
