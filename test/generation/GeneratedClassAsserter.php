<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Generation;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\Assert;

use function array_pop;
use function explode;
use function is_array;
use function strpos;

final class GeneratedClassAsserter
{
    private NodeFinder $nodeFinder;
    /** @var Stmt[] */
    private array $statements;

    public function __construct(InMemoryFileWriter $fileWriter, string $path)
    {
        $this->nodeFinder = new NodeFinder();
        $phpParser        = (new ParserFactory())->createForVersion(PhpVersion::fromString('7.0'));
        $phpCode          = $fileWriter->getContentsByFullPath($path);
        $statements       = $phpParser->parse($phpCode);
        if ($statements === null) {
            throw new InvalidArgumentException('No statements found in provided PHP code');
        }

        $this->statements = $statements;
    }

    public function assertHasName(string $name): void
    {
        $classOrInterfaceWithGivenName = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => ($node instanceof Class_ || $node instanceof Interface_)
                && $node->name !== null && $node->name->toString() === $name,
        );

        Assert::assertNotNull($classOrInterfaceWithGivenName, sprintf('Class or interface with name %s not found', $name));
    }

    public function assertInNamespace(string $namespace): void
    {
        $foundNamespace = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => $node instanceof Namespace_ &&
                $node->name !== null && $node->name->toString() === $namespace,
        );

        Assert::assertNotNull($foundNamespace, sprintf('Class or interface in namespace %s not found', $namespace));
    }

    private function hasUseStatement(string $fqcn, ?string $alias = null): bool
    {
        $useStatement = $this->nodeFinder->findFirst(
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

                    if ($alias === null) {
                        return true;
                    }

                    return $use->alias !== null && $use->alias->toString() === $alias;
                }

                return false;
            }
        );

        return $useStatement !== null;
    }

    public function assertHasUseStatement(string $fqcn, ?string $alias = null): void
    {
        Assert::assertTrue($this->hasUseStatement($fqcn, $alias), sprintf('There is no use statement with class %s', $fqcn));
    }

    public function assertExtends(string $extendedClassFQCN): void
    {
        $extendedClassFQCNArray = explode('\\', $extendedClassFQCN);
        $extendedClassShortName = array_pop($extendedClassFQCNArray);

        $useStatementFoundForClass = $this->hasUseStatement($extendedClassFQCN);

        $extendedClass = $this->nodeFinder->findFirst(
            $this->statements,
            static function (Node $node) use ($extendedClassFQCN, $extendedClassShortName, $useStatementFoundForClass): bool {
                if (! $node instanceof Class_ && ! $node instanceof Interface_) {
                    return false;
                }

                if (
                    $node instanceof Class_ &&
                    $node->extends !== null
                ) {
                    if ($node->extends->toString() === $extendedClassFQCN) {
                        return true;
                    }

                    return $useStatementFoundForClass && $node->extends->toString() === $extendedClassShortName;
                }

                if (! is_array($node->extends)) {
                    return false;
                }

                /** @var Name $extend */
                foreach ($node->extends as $extend) {
                    if ($extend->toString() === $extendedClassFQCN) {
                        return true;
                    }

                    if ($useStatementFoundForClass && $extend->toString() === $extendedClassShortName) {
                        return true;
                    }
                }

                return false;
            }
        );

        Assert::assertNotNull($extendedClass, sprintf('There is no class that extends class %s', $extendedClassFQCN));
    }

    public function assertImplements(string $implementedInterfaceFQCN): void
    {
        $implementedInterfaceFQCNArray = explode('\\', $implementedInterfaceFQCN);
        $implementedInterfaceShortName = array_pop($implementedInterfaceFQCNArray);

        $useStatementFoundForInterface = $this->hasUseStatement($implementedInterfaceFQCN);

        $implementedInterface = $this->nodeFinder->findFirst(
            $this->statements,
            static function (Node $node) use ($implementedInterfaceFQCN, $implementedInterfaceShortName, $useStatementFoundForInterface): bool {
                if (! $node instanceof Class_) {
                    return false;
                }

                /** @var Name $implement */
                foreach ($node->implements as $implement) {
                    if ($implement->toString() === $implementedInterfaceFQCN) {
                        return true;
                    }

                    if ($implement->toString() === $implementedInterfaceShortName && $useStatementFoundForInterface) {
                        return true;
                    }
                }

                return false;
            }
        );

        Assert::assertNotNull($implementedInterface, sprintf('There is no class that implements interface %s', $implementedInterfaceFQCN));
    }

    public function assertHasProperty(string $propertyName, string $type, bool $isNullable): void
    {
        $property = $this->nodeFinder->findFirst(
            $this->statements,
            static function (Node $node) use ($propertyName, $type, $isNullable): bool {
                if (! $node instanceof Property) {
                    return false;
                }

                $propertyType = '';

                switch (true) {
                    case $node->type instanceof Identifier:
                    case $node->type instanceof Name:
                        $propertyType = $node->type->toString();
                        break;
                    case $node->type instanceof NullableType:
                        $propertyType = $node->type->type->toString();
                }

                if ($propertyType !== $type) {
                    return false;
                }

                /** @var PropertyProperty $prop */
                foreach ($node->props as $prop) {
                    if ($prop->name->toString() !== $propertyName) {
                        continue;
                    }

                    return $isNullable ? $node->type instanceof NullableType : ! $node->type instanceof NullableType;
                }

                return false;
            }
        );

        Assert::assertNotNull($property, sprintf('There is no class that has property %s with type %s', $propertyName, $type));
    }

    public function assertPropertyDocblockContains(string $propertyName, string $docblockRow): void
    {
        $propertyWithDocblock = $this->nodeFinder->findFirst(
            $this->statements,
            static function (Node $node) use ($propertyName, $docblockRow): bool {
                if (! $node instanceof Property) {
                    return false;
                }

                /** @var PropertyProperty $prop */
                foreach ($node->props as $prop) {
                    if ($prop->name->toString() !== $propertyName) {
                        continue;
                    }

                    return $node->getDocComment() !== null && strpos($node->getDocComment()->getText(), $docblockRow) !== false;
                }

                return false;
            }
        );

        Assert::assertNotNull($propertyWithDocblock, sprintf('There is no property named %s that contains docblock %s', $propertyName, $docblockRow));
    }

    public function assertHasMethod(string $methodName): void
    {
        $method = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => $node instanceof ClassMethod && $node->name->toString() === $methodName,
        );

        Assert::assertNotNull($method, sprintf('Method with name %s not found', $methodName));
    }

    public function assertMethodDocblockContains(string $methodName, string $docblockRow): void
    {
        $methodWithDocblock = $this->nodeFinder->findFirst(
            $this->statements,
            static fn (Node $node): bool => $node instanceof ClassMethod &&
                $node->name->toString() === $methodName && $node->getDocComment() !== null &&
                (strpos($node->getDocComment()->getText(), $docblockRow) !== false)
        );

        Assert::assertNotNull($methodWithDocblock, sprintf('There is no method named %s that contains docblock %s', $methodName, $docblockRow));
    }

    public function assertMethodReturns(string $methodName, string $type, bool $isNullable): void
    {
        $typeShortName            = '';
        $useStatementFoundForType = false;
        if (strpos($type, '\\') !== false) {
            $useStatementFoundForType = $this->hasUseStatement($type);
            $typeFQCNArray            = explode('\\', $type);
            $typeShortName            = array_pop($typeFQCNArray);
        }

        $methodWitReturnedValue = $this->nodeFinder->findFirst(
            $this->statements,
            static function (Node $node) use ($methodName, $type, $isNullable, $useStatementFoundForType, $typeShortName): bool {
                if (! $node instanceof ClassMethod) {
                    return false;
                }

                if ($node->name->toString() !== $methodName || $node->getReturnType() === null) {
                    return false;
                }

                $returnType = '';

                switch (true) {
                    case $node->getReturnType() instanceof Identifier:
                    case $node->getReturnType() instanceof Name:
                        $returnType = $node->getReturnType()->toString();
                        break;
                    case $node->getReturnType() instanceof NullableType:
                        $returnType = $node->getReturnType()->type->toString();
                        break;
                }

                if (! $useStatementFoundForType && $returnType !== $type) {
                    return false;
                }

                if ($useStatementFoundForType && $returnType !== $typeShortName) {
                    return false;
                }

                return $isNullable ? $node->getReturnType() instanceof NullableType : ! $node->getReturnType() instanceof NullableType;
            }
        );

        Assert::assertNotNull($methodWitReturnedValue, sprintf('There is no method named %s that returns %s', $methodName, $type));
    }

    public function assertMethodHasArgument(string $methodName, string $argumentName, string $type, bool $isNullable): void
    {
        $typeShortName            = '';
        $useStatementFoundForType = false;
        if (strpos($type, '\\') !== false) {
            $useStatementFoundForType = $this->hasUseStatement($type);
            $typeFQCNArray            = explode('\\', $type);
            $typeShortName            = array_pop($typeFQCNArray);
        }

        $argumentFound = $this->nodeFinder->findFirst(
            $this->statements,
            static function (Node $node) use ($methodName, $argumentName, $type, $isNullable, $typeShortName, $useStatementFoundForType): bool {
                if (! $node instanceof ClassMethod) {
                    return false;
                }

                if ($node->name->toString() !== $methodName) {
                    return false;
                }

                /** @var Param $param */
                foreach ($node->params as $param) {
                    if ($param->var === null || $param->type === null) {
                        return false;
                    }

                    if (! $param->var instanceof Variable || $param->var->name !== $argumentName) {
                        return false;
                    }

                    $argumentType = '';

                    switch (true) {
                        case $param->type instanceof Identifier:
                        case $param->type instanceof Name:
                            $argumentType = $param->type->toString();
                            break;
                        case $param->type instanceof NullableType:
                            $argumentType = $param->type->type->toString();
                    }

                    if (! $useStatementFoundForType && $argumentType !== $type) {
                        return false;
                    }

                    if ($useStatementFoundForType && $argumentType !== $typeShortName) {
                        return false;
                    }

                    return $isNullable ? $param->type instanceof NullableType : ! $param->type instanceof NullableType;
                }

                return false;
            }
        );

        Assert::assertNotNull($argumentFound, sprintf('There is no method named %s that have argument %s with type %s', $methodName, $argumentName, $type));
    }
}
