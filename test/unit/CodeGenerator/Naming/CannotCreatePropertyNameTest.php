<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Naming;

use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\CannotCreatePropertyName;
use PHPUnit\Framework\TestCase;

use function sprintf;

/** @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Naming\CannotCreatePropertyName */
final class CannotCreatePropertyNameTest extends TestCase
{
    public function testBecauseTextContainsNoValidSymbolsShowsCorrectError(): void
    {
        $text = 'Some random text';

        $exceptionMessage = sprintf(
            'Cannot create property name from text: %s. Text contains no characters that can be used.',
            $text
        );

        $this->expectException(CannotCreatePropertyName::class);
        $this->expectExceptionMessage($exceptionMessage);

        throw CannotCreatePropertyName::becauseTextContainsNoValidSymbols($exceptionMessage);
    }

    public function testBecauseIsNotValidPhpPropertyNameShowsCorrectError(): void
    {
        $text = 'Some random text';

        $exceptionMessage = sprintf(
            'Cannot create property name from: %s. String is not a valid PHP property name.',
            $text
        );

        $this->expectException(CannotCreatePropertyName::class);
        $this->expectExceptionMessage($exceptionMessage);

        throw CannotCreatePropertyName::becauseIsNotValidPhpPropertyName($exceptionMessage);
    }
}
