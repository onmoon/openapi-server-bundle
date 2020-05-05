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
        while (array_search($reference, $this->references) !== false) {
            $reference = $this->rename($reference);
        }

        $this->references[$fullName] = $reference;
        $use = new Use_($fullName, UseStmt::TYPE_NORMAL);
        if($reference !== $class->getClassName()) {
            $use->as($reference);
        }
        $this->addStmt($use);
        return $reference;
    }

    private function rename(string $class) : string {
        if(substr($class, -1) === '_') {
            return $class.'1';
        } elseif(preg_match('"_(\d+)$"', $class, $match)) {
            return preg_replace('"_\d+$"', $class, '_'.($match[1]+1));
        } else {
            return $class.'_';
        }

    }
}
