<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Generation;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Assert;

use function array_filter;
use function current;
use function strpos;

final class GeneratedClassAsserter
{
    private NodeFinder $nodeFinder;
    /** @var Stmt[] */
    private ?array $statements;

    public function __construct(InMemoryFileWriter $fileWriter, string $path)
    {
        $phpParser        = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->nodeFinder = new NodeFinder();
        $phpCode          = $fileWriter->getContentsByFullPath($path);
        $statements       = $phpParser->parse($phpCode);
        if ($statements === null) {
            throw new InvalidArgumentException('No statements found in provided PHP code');
        }

        $this->statements = $statements;
    }

    public function assertHasName(string $name): void
    {
        $nameFound = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => ($node instanceof Class_ || $node instanceof Interface_)
                && $node->name !== null && $node->name->toString() === $name,
        );

        Assert::assertTrue($nameFound);
    }

    public function assertInNamespace(string $namespace): void
    {
        $namespaceFound = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => ($node instanceof Class_ || $node instanceof Interface_) &&
                $node->namespacedName->toString() === $namespace,
        );

        Assert::assertTrue($namespaceFound);
    }

    public function assertHasUseStatement(string $fqcn, ?string $alias = null): void
    {
        $nameFound = $this->nodeFinder->findFirst(
            $this->statements,
            static function (Node $node) use ($fqcn, $alias): bool {
                if (! $node instanceof Use_) {
                    return false;
                }

                /** @var UseUse $use */
                foreach ($node->uses as $use) {
                    if ($use->name->toString() !== $fqcn) {
                        continue;
                    }

                    return $alias === null ? true : $use->alias === $alias;
                }

                return false;
            }
        );

        Assert::assertTrue($nameFound);
    }

    public function assertExtends(string $extendedClassFQCN): void
    {
        $extendedClassFound = $this->nodeFinder->findFirst(
            $this->statements,
            static function (Node $node) use ($extendedClassFQCN): bool {
                if (! $node instanceof Class_) {
                    return false;
                }

                if ($node->extends === null) {
                    return false;
                }

                /** @var Name $extend */
                foreach ($node->extends as $extend) {
                    return $extend->toString() === $extendedClassFQCN;
                }

                return false;
            }
        );

        Assert::assertTrue($extendedClassFound);
    }

    public function assertImplements(string $implementedClassFQCN): void
    {
        $implementedClassFound = $this->nodeFinder->findFirst(
            $this->statements,
            static function (Node $node) use ($implementedClassFQCN): bool {
                if (! $node instanceof Class_) {
                    return false;
                }

                /** @var Name $implement */
                foreach ($node->implements as $implement) {
                    if ($implement->toString() === $implementedClassFQCN) {
                        return true;
                    }
                }

                return false;
            }
        );
        Assert::assertTrue($implementedClassFound);
    }

    public function assertHasProperty(string $propertyName, string $type, bool $isNullable): void
    {
        $propertyFound = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => $node instanceof Property &&
                $node->name === $propertyName && $node->type->toString() === $type &&
            $isNullable ? $node->type instanceof NullableType : ! $node->type instanceof NullableType,
        );

        Assert::assertTrue($propertyFound);
    }

    public function assertPropertyDocblockContains(string $propertyName, string $docblockRow): void
    {
        $propertyDocblockFound = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => $node instanceof Property &&
            $node->name === $propertyName && strpos($node->getDocComment()->getText(), $docblockRow) !== false
        );

        Assert::assertTrue($propertyDocblockFound);
    }

    public function assertHasMethod(string $methodName): void
    {
        $methodFound = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => $node instanceof ClassMethod &&
            $node->name === $methodName,
        );

        Assert::assertTrue($methodFound);
    }

    public function assertMethodDocblockContains(string $methodName, string $docblockRow): void
    {
        $methodDocblockFound = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => $node instanceof ClassMethod &&
                $node->name === $methodName && strpos($node->getDocComment()->getText(), $docblockRow) !== false
        );

        Assert::assertTrue($methodDocblockFound);
    }

    public function assertMethodReturns(string $methodName, string $type, bool $isNullable): void
    {
        $returnedValueFound = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => $node instanceof ClassMethod &&
                $node->name === $methodName && $node->getReturnType()->toString() === $type &&
            $isNullable ? $node->type instanceof NullableType : ! $node->type instanceof NullableType
        );

        Assert::assertTrue($returnedValueFound);
    }

    public function assertMethodHasArgument(string $methodName, string $argumentName, string $type, bool $isNullable): void
    {
        $argumentFound = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => $node instanceof ClassMethod &&
                $node->name === $methodName && (bool) current(array_filter(
                    $node->params,
                    static function (Param $param) use ($argumentName, $type, $isNullable): bool {
                        return $param->name === $argumentName && $param->type->toString() === $type &&
                        $isNullable ? $param->type instanceof NullableType : ! $param->type instanceof NullableType;
                    }
                ))
        );

        Assert::assertTrue($argumentFound);
    }
}
