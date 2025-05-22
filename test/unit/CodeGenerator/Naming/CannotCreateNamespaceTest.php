<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Naming;

use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\CannotCreateNamespace;
use PHPUnit\Framework\TestCase;

/** @covers  \OnMoon\OpenApiServerBundle\CodeGenerator\Naming\CannotCreateNamespace */
final class CannotCreateNamespaceTest extends TestCase
{
    public function testBecauseTestContainsNoValidSymbolsShowCorrectError(): void
    {
        $text = 'Some random text';

        $exceptionMessage = sprintf(
            'Cannot create namespace from text: %s. Text contains no characters that can be used.',
            $text
        );

        $this->expectException(CannotCreateNamespace::class);
        $this->expectExceptionMessage($exceptionMessage);

        throw CannotCreateNamespace::becauseTextContainsNoValidSymbols($exceptionMessage);
    }
}
