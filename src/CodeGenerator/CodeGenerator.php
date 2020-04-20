<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\OpenApi\ScalarTypesResolver;
use PhpParser\Builder\Namespace_;
use PhpParser\BuilderFactory;

abstract class CodeGenerator
{
    protected const AUTOGENERATED_WARNING = '/**
      * This %s was automatically generated
      * You should not change it manually as it will be overwritten
      */';

    protected BuilderFactory $factory;
    protected ScalarTypesResolver $typeResolver;
    protected string $languageLevel;
    protected bool $fullDocs = false;

    /**
     * DtoGenerator constructor.
     * @param BuilderFactory $factory
     * @param ScalarTypesResolver $typeResolver
     * @param string $languageLevel
     */
    public function __construct(BuilderFactory $factory, ScalarTypesResolver $typeResolver, string $languageLevel)
    {
        $this->factory = $factory;
        $this->typeResolver = $typeResolver;
        $this->languageLevel = $languageLevel;
    }

    public function use(Namespace_ $builder, string $parentNameSpace, ClassDefinition $class)
    {
        if ($parentNameSpace === $class->getNamespace()) {
            return;
        }
        $builder->addStmt($this->factory->use($class->getFQCN()));
    }

    public function getTypeDocBlock(PropertyDefinition $definition): string
    {
        return $this->getTypeName($definition) .
            ($definition->isArray() ? '[]' : '') .
            ($definition->isNullable() ? '|null' : '');
    }

    public function getTypePhp(PropertyDefinition $definition): string
    {
        return
            ($definition->isNullable() ? '?' : '') .
            ($definition->isArray() ? 'array' : $this->getTypeName($definition));
    }

    public function getTypeName(PropertyDefinition $definition): string
    {
        if ($definition->getObjectTypeDefinition() !== null) {
            return $definition->getObjectTypeDefinition()->getClassName();
        } else {
            return $this->typeResolver->getPhpType($definition->getScalarTypeId());
        }
    }

    public function getDocComment(array $lines): string
    {
        if (count($lines) === 1) {
            return sprintf('/** %s */', trim($lines[0]));
        } else {
            return implode(
                PHP_EOL,
                [
                    '/**',
                    ...array_map(
                    //ToDo: add space after * anyway after tests
                        static fn(string $line): string => ' *' . (trim($line)?' ':'') . trim($line),
                        $lines
                    ),
                    ' */',
                ]
            );
        }
    }
}