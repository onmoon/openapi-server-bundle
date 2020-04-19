<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\OpenApi\ScalarTypesResolver;
use PhpParser\Builder\Namespace_;
use PhpParser\Builder\Property;
use PhpParser\BuilderFactory;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\PrettyPrinter\Standard;

class DtoGenerator
{
    private BuilderFactory $factory;
    private ScalarTypesResolver $typeResolver;
    private string $languageLevel;

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


    public function generate(DtoDefinition $definition) : GeneratedClass {
        $fileBuilder = $this
            ->factory
            ->namespace($definition->getNamespace());

        $classBuilder = $this
            ->factory
            ->class($definition->getClassName())
            ->makeFinal()
            ->setDocComment('/**
                              * This class was automatically generated
                              * You should not change it manually as it will be overwritten
                              */');

        if($definition->getImplements() !== null) {
            $classBuilder->implement($definition->getImplements()->getClassName());
            $this->use($fileBuilder, $definition->getImplements());
        }

        foreach ($definition->getProperties() as $property) {
            $classBuilder->addStmt($this->generateClassProperty($property));
            if($property->getObjectTypeDefinition() !== null) {
                $this->use($fileBuilder, $property->getObjectTypeDefinition());
            }
        }

        $fileBuilder = $fileBuilder->addStmt($classBuilder);

        return new GeneratedClass(
            $definition->getFilePath(),
            $definition->getFileName(),
            $definition->getNamespace(),
            $definition->getClassName(),
            (new Standard())->prettyPrintFile([
                new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]),
                $fileBuilder->getNode(),
            ])
        );
    }

    private function use(Namespace_ $builder, ClassDefinition $class) {
        $builder->addStmt($this->factory->use($class->getFQCN()));
    }

    private function generateClassProperty(PropertyDefinition $definition) : Property
    {
        $property = $this->factory
            ->property($definition->getClassPropertyName())
            ->makePrivate();

        if ($definition->getDefaultValue() !== null) {
            $property->setDefault($definition->getDefaultValue());
        } elseif ($definition->isNullable()) {
            $property->setDefault(null);
        }

        $docCommentLines = [];

        if ($definition->getDescription() !== null) {
            $docCommentLines[] = sprintf(' %s', $definition->getDescription());
        }

        if (version_compare($this->languageLevel, '7.4.0') >= 0) {
            $property->setType(
                ($definition->isNullable() && $definition->getDefaultValue() === null ? '?' : '') .
                ($definition->isArray() ? 'array' : $this->getTypeName($definition))
            );
        }

        if (count($docCommentLines) > 0) {
            $docCommentLines[] = '';
        }

        $docCommentLines[] = sprintf(
            ' @var %s%s%s $%s ',
            $this->getTypeName($definition),
            $definition->isArray() ? '[]' : '',
            $definition->isNullable() && $definition->getDefaultValue() === null ? '|null' : '',
            $definition->getClassPropertyName()
        );


        if (count($docCommentLines) === 1) {
            $property->setDocComment(
                sprintf('/** %s */', trim($docCommentLines[0]))
            );
        } else {
            $property->setDocComment(
                implode(
                    PHP_EOL,
                    [
                        '/**',
                        ...array_map(
                            static fn (string $docCommentLine) : string => ' *' . rtrim($docCommentLine),
                            $docCommentLines
                        ),
                        ' */',
                    ]
                )
            );
        }

        return $property;
    }

    private function getTypeName(PropertyDefinition $definition) : string {
        if($definition->getObjectTypeDefinition() !== null) {
            return $definition->getObjectTypeDefinition()->getClassName();
        } else {
            return $this->typeResolver->getPhpType($definition->getScalarTypeId());
        }
    }
}
