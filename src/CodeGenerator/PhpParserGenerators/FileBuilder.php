<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use PhpParser\Builder;
use PhpParser\Builder\Namespace_;
use PhpParser\Builder\Use_;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_ as UseStmt;

use function array_search;
use function Safe\preg_match;
use function Safe\preg_replace;
use function Safe\substr;

class FileBuilder
{
    private ClassDefinition $definition;
    private Namespace_ $namespace;
    /** @var string[] */
    private array $references = [];

    public function __construct(ClassDefinition $definition)
    {
        $this->definition = $definition;
        $this->getReference($definition);
        $this->namespace = new Namespace_($definition->getNamespace());
    }

    public function getReference(ClassDefinition $class): string
    {
        $fullName = $class->getFQCN();

        if (isset($this->references[$fullName])) {
            return $this->references[$fullName];
        }

        $reference = $class->getClassName();
        $rename    = false;
        while (array_search($reference, $this->references, true) !== false) {
            $reference = $this->rename($reference);
            $rename    = true;
        }

        $this->references[$fullName] = $reference;
        if ($class->getNamespace() !== $this->definition->getNamespace() || $rename) {
            $use = new Use_($fullName, UseStmt::TYPE_NORMAL);
            if ($rename) {
                $use->as($reference);
            }

            $this->addStmt($use);
        }

        return $reference;
    }

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress PossiblyNullArrayAccess
     * @psalm-suppress InvalidReturnStatement
     */
    private function rename(string $class): string
    {
        if (substr($class, -1) === '_') {
            return $class . '1';
        }

        if (preg_match('/_(\d+)$/', $class, $match) === 1) {
            /** @psalm-var numeric-string $oldNumber */
            $oldNumber = $match[1];

            return preg_replace('"_\d+$"', '_' . (string) ((int) $oldNumber + 1), $class);
        }

        return $class . '_';
    }

    /**
     * Adds a statement.
     *
     * @param Node|Builder $stmt The statement to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addStmt($stmt): self
    {
        $this->namespace->addStmt($stmt);

        return $this;
    }

    public function getNamespace(): Namespace_
    {
        return $this->namespace;
    }
}
