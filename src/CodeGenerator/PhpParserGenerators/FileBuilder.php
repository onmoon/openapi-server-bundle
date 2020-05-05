<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators;


use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use PhpParser\Builder\Namespace_;
use PhpParser\Builder\Use_;
use \PhpParser\Node\Stmt\Use_ as UseStmt;

class FileBuilder extends Namespace_
{
    private ClassDefinition $definition;
    /** @var string[] */
    private array $references = [];

    public function __construct(ClassDefinition $definition)
    {
        $this->definition = $definition;
        $this->getReference($definition);
        parent::__construct($definition->getNamespace());
    }

    public function getReference(ClassDefinition $class) : string {
        $fullName = $class->getFQCN();

        if(isset($this->references[$fullName])) {
            return $this->references[$fullName];
        }

        $reference = $class->getClassName();
        $rename = false;
        while (array_search($reference, $this->references) !== false) {
            $reference = $this->rename($reference);
            $rename = true;
        }

        $this->references[$fullName] = $reference;
        if($class->getNamespace() !== $this->definition->getNamespace() || $rename) {
            $use = new Use_($fullName, UseStmt::TYPE_NORMAL);
            if ($rename) {
                $use->as($reference);
            }
            $this->addStmt($use);
        }
        return $reference;
    }

    private function rename(string $class) : string {
        if(substr($class, -1) === '_') {
            return $class.'1';
        } elseif(preg_match('"_(\d+)$"', $class, $match)) {
            $oldNumber = (int)$match[1];
            return preg_replace('"_\d+$"', '_'.($oldNumber+1), $class);
        } else {
            return $class.'_';
        }

    }
}
