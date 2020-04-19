<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\OpenApi\ScalarTypesResolver;
use PhpParser\Builder\Method;
use PhpParser\Builder\Namespace_;
use PhpParser\Builder\Param;
use PhpParser\Builder\Property;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Return_;
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


    public function generate(DtoDefinition $definition): GeneratedClass
    {
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

        if ($definition->getImplements() !== null) {
            $classBuilder->implement($definition->getImplements()->getClassName());
            $this->use($fileBuilder, $definition->getImplements());
        }

        foreach ($definition->getProperties() as $property) {
            if ($property->getObjectTypeDefinition() !== null) {
                $this->use($fileBuilder, $property->getObjectTypeDefinition());
            }
        }

        $classBuilder->addStmts($this->generateProperties($definition));
        $classBuilder->addStmts($this->generateConstructor($definition));
        $classBuilder->addStmts($this->generateGetters($definition));
        $classBuilder->addStmts($this->generateSetters($definition));

        if ($definition instanceof ResponseDtoDefinition) {
            $classBuilder->addStmt($this->generateResponseCodeStaticMethod($definition));
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

    private function use(Namespace_ $builder, ClassDefinition $class)
    {
        $builder->addStmt($this->factory->use($class->getFQCN()));
    }

    /**
     * @return Node[]
     */
    private function generateProperties(DtoDefinition $definition): array {
        $properties = [];
        foreach ($definition->getProperties() as $property) {
            $properties[] = $this->generateClassProperty($property);
        }
        return $properties;
    }

    /**
     * @return Node[]
     */
    private function generateGetters(DtoDefinition $definition): array {
        $properties = [];
        foreach ($definition->getProperties() as $property) {
            $properties[] = $this->generateGetter($property);
        }
        return $properties;
    }

    /**
     * @return Node[]
     */
    private function generateSetters(DtoDefinition $definition): array {
        $properties = [];
        foreach ($definition->getProperties() as $property) {
            $properties[] = $this->generateSetter($property);
        }
        return $properties;
    }

    /**
     * @return Node[]
     */
    private function generateConstructor(DtoDefinition $definition): array {
        $constructorBuilder   = $this->factory->method('__construct')->makePublic();
        $constructorDocs = [];

        foreach ($definition->getProperties() as $property) {
            $constructorBuilder
                ->addParam($this->generateMethodParameter($property))
                ->addStmt($this->getAssignmentDefinition($property->getClassPropertyName()));
            $constructorDocs[] = sprintf(
                '@param %s $%s',
                $this->getTypeDocBlock($property),
                $property->getClassPropertyName()
            );
        }
        $constructorBuilder->setDocComment($this->getDocComment($constructorDocs));
        return [$constructorBuilder];
    }

    private function generateClassProperty(PropertyDefinition $definition): Property
    {
        $property = $this->factory
            ->property($definition->getClassPropertyName())
            ->makePrivate();

        if ($definition->getDefaultValue() !== null) {
            $property->setDefault($definition->getDefaultValue());
        } elseif ($definition->isNullable()) {
            $property->setDefault(null);
        }

        $property->setType($this->getTypePhp($definition));

        $docCommentLines = [];

        if ($definition->getDescription() !== null) {
            $docCommentLines[] = sprintf('%s', $definition->getDescription());
            $docCommentLines[] = '';
        }

        $docCommentLines[] = sprintf(
            '@var %s $%s ',
            $this->getTypeDocBlock($definition),
            $definition->getClassPropertyName()
        );

        $property->setDocComment($this->getDocComment($docCommentLines));
        return $property;
    }

    private function generateMethodParameter(PropertyDefinition $definition): Param
    {
        return $this
            ->factory
            ->param($definition->getClassPropertyName())
            ->setType($this->getTypePhp($definition));
    }

    private function getAssignmentDefinition(string $name): Assign
    {
        return new Assign(
            new Variable('this->' . $name),
            new Variable($name)
        );
    }

    private function generateGetter(PropertyDefinition $definition): Method
    {
        $method = $this->factory
            ->method($definition->getGetterName())
            ->makePublic()
            ->setReturnType($this->getTypePhp($definition))
            ->addStmt(new Return_(new Variable('this->' . $definition->getClassPropertyName())));

        $method->setDocComment(
            $this->getDocComment(['@return ' . $this->getTypeDocBlock($definition)])
        );

        return $method;
    }

    private function generateSetter(PropertyDefinition $definition): Method
    {
        $method = $this->factory
            ->method($definition->getSetterName())
            ->makePublic()
            ->setReturnType('self')
            ->addParam($this->generateMethodParameter($definition))
            ->addStmt($this->getAssignmentDefinition($definition->getClassPropertyName()))
            ->addStmt(new Return_(new Variable('this')));

        $method->setDocComment(
            $this->getDocComment([
                sprintf(
                    '@param %s $%s',
                    $this->getTypeDocBlock($definition),
                    $definition->getClassPropertyName()
                ),
                '@return self'
            ])
        );

        return $method;
    }

    private function getTypeDocBlock(PropertyDefinition $definition): string
    {
        return $this->getTypeName($definition) .
            ($definition->isArray() ? '[]' : '') .
            ($definition->isNullable() ? '|null' : '');
    }

    private function getTypePhp(PropertyDefinition $definition): string
    {
        return
            ($definition->isNullable() ? '?' : '') .
            ($definition->isArray() ? 'array' : $this->getTypeName($definition));
    }

    private function getTypeName(PropertyDefinition $definition): string
    {
        if ($definition->getObjectTypeDefinition() !== null) {
            return $definition->getObjectTypeDefinition()->getClassName();
        } else {
            return $this->typeResolver->getPhpType($definition->getScalarTypeId());
        }
    }

    private function generateResponseCodeStaticMethod(ResponseDtoDefinition $definition): Method
    {
        $responseCode = (int)$definition->getStatusCode();
        return $this
            ->factory
            ->method('_getResponseCode')
            ->makePublic()
            ->makeStatic()
            ->setReturnType('?int')
            ->addStmt(
                new Return_(
                    $this->factory->val($responseCode !== 0 ? $responseCode : null)
                )
            )
            ->setDocComment(
                $this->getDocComment(['@return ?int'])
            );
    }

    private function getDocComment(array $lines): string
    {
        if (count($lines) === 1) {
            return sprintf('/** %s */', trim($lines[0]));
        } else {
            return implode(
                PHP_EOL,
                [
                    '/**',
                    ...array_map(
                        static fn(string $line): string => ' * ' . trim($line),
                        $lines
                    ),
                    ' */',
                ]
            );
        }
    }
}
