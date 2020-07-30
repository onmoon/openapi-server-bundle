<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Generation\Types;

use OnMoon\OpenApiServerBundle\Test\Generation\GenerationTestCase;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\Assert;

final class TypesGenerationTest extends GenerationTestCase
{
    public function testStringTypeGeneration(): void
    {
        $generatedFiles = $this->generateCodeFromSpec(__DIR__ . '/specification.yaml');
        $responseDto    = $generatedFiles->getContentsByFullPath('/test/Apis/TestApi/GetTest/Dto/Response/OK/GetTestOKDto.php');
        $statements     = $this->getStatements($responseDto);

        /** @var ClassMethod $stringPropertyGetterMethod */
        $stringPropertyGetterMethod = $this->nodeFinder()->findFirst(
            $statements,
            static fn (Node $node): bool => $node instanceof ClassMethod &&
                $node->name->name === 'getStringProperty'
        );

        /** @var Identifier $stringPropertyGetterMethodReturnType */
        $stringPropertyGetterMethodReturnType = $stringPropertyGetterMethod->returnType;

        Assert::assertEquals('string', $stringPropertyGetterMethodReturnType->name);
    }
}
