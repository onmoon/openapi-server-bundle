<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Generation;

use InvalidArgumentException;
use PhpParser\Node\Stmt;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

abstract class GenerationTestCase extends TestCase
{
    private Parser $phpParser;
    private NodeFinder $nodeFinder;

    public function setUp(): void
    {
        $this->phpParser  = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->nodeFinder = new NodeFinder();
    }

    protected function generateCodeFromSpec(
        string $specificationPath,
        string $nameSpace = 'TestApi'
    ): InMemoryFileWriter {
        $fileWriter = new InMemoryFileWriter();

        $codeGenerator = TestApiServerCodeGeneratorFactory::getCodeGenerator(
            [
                'test' => [
                    'path' => $specificationPath,
                    'name_space' => $nameSpace,
                    'media_type' => 'application/json',
                ],
            ],
            $fileWriter
        );

        $codeGenerator->generate();

        return $fileWriter;
    }

    /** @return Stmt[] */
    protected function getStatements(string $phpCode): array
    {
        $statements = $this->phpParser->parse($phpCode);

        if ($statements === null) {
            throw new InvalidArgumentException('No statements found in provided PHP code');
        }

        return $statements;
    }

    protected function nodeFinder(): NodeFinder
    {
        return $this->nodeFinder;
    }
}
