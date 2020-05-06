<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators;

use Exception;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use PhpParser\Builder;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Builder\Property;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param as Param_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use function array_map;
use function count;
use function Safe\sprintf;

class DtoCodeGenerator extends CodeGenerator
{
    public function generate(DtoDefinition $definition) : GeneratedFileDefinition
    {
        $fileBuilder = new FileBuilder($definition);

        $classBuilder = $this
            ->factory
            ->class($fileBuilder->getReference($definition))
            ->makeFinal()
            ->setDocComment(sprintf(self::AUTOGENERATED_WARNING, 'class'));

        $implements = $definition->getImplements();
        if ($implements !== null) {
            $classBuilder->implement($fileBuilder->getReference($implements));
        }

        $classBuilder->addStmts($this->generateProperties($fileBuilder, $definition));
        $classBuilder->addStmts($this->generateConstructor($fileBuilder, $definition));
        $classBuilder->addStmts($this->generateGetters($fileBuilder, $definition));
        $classBuilder->addStmts($this->generateSetters($fileBuilder, $definition));

        if ($definition instanceof ResponseDtoDefinition) {
            $classBuilder->addStmt($this->generateResponseCodeStaticMethod($definition));
        }

        $classBuilder->addStmt($this->generateToArray($fileBuilder, $definition));
        $classBuilder->addStmt($this->generateFromArray($fileBuilder, $definition));

        $fileBuilder = $fileBuilder->addStmt($classBuilder);

        return new GeneratedFileDefinition(
            $definition,
            $this->printFile($fileBuilder)
        );
    }

    /**
     * @return Builder[]
     */
    private function generateProperties(FileBuilder $builder, DtoDefinition $definition) : array
    {
        $properties = [];
        foreach ($definition->getProperties() as $property) {
            $properties[] = $this->generateClassProperty($builder, $property);
        }

        return $properties;
    }

    /**
     * @return Builder[]
     */
    private function generateGetters(FileBuilder $builder, DtoDefinition $definition) : array
    {
        $properties = [];
        foreach ($definition->getProperties() as $property) {
            if (! $property->hasGetter()) {
                continue;
            }

            $properties[] = $this->generateGetter($builder, $property);
        }

        return $properties;
    }

    /**
     * @return Builder[]
     */
    private function generateSetters(FileBuilder $builder, DtoDefinition $definition) : array
    {
        $properties = [];
        foreach ($definition->getProperties() as $property) {
            if (! $property->hasSetter()) {
                continue;
            }

            $properties[] = $this->generateSetter($builder, $property);
        }

        return $properties;
    }

    /**
     * @return Builder[]
     */
    private function generateConstructor(FileBuilder $builder, DtoDefinition $definition) : array
    {
        $constructorBuilder = $this->factory->method('__construct')->makePublic();
        $constructorDocs    = [];
        $constructorEmpty   = true;

        foreach ($definition->getProperties() as $property) {
            if (! $property->isInConstructor()) {
                continue;
            }

            $constructorEmpty = false;
            $constructorBuilder
                ->addParam($this->generateMethodParameter($builder, $property))
                ->addStmt($this->getAssignmentDefinition($property->getClassPropertyName()));
            if (! $this->fullDocs && ! $property->isArray()) {
                continue;
            }

            $constructorDocs[] = sprintf(
                '@param %s $%s',
                $this->getTypeDocBlock($builder, $property),
                $property->getClassPropertyName()
            );
        }

        if ($constructorEmpty) {
            return [];
        }

        if (count($constructorDocs) > 0) {
            $constructorBuilder->setDocComment($this->getDocComment($constructorDocs));
        }

        return [$constructorBuilder];
    }

    private function generateClassProperty(FileBuilder $builder, PropertyDefinition $definition) : Property
    {
        $property = $this->factory
            ->property($definition->getClassPropertyName())
            ->makePrivate();

        if ($definition->isNullable()) {
            $property->setDefault(null);
        }

        $property->setType($this->getTypePhp($builder, $definition));

        $docCommentLines = [];

        if ($definition->getDescription() !== null) {
            $docCommentLines[] = sprintf('%s', $definition->getDescription());
            $docCommentLines[] = '';
        }

        if ($this->fullDocs || $definition->isArray()) {
            $docCommentLines[] = sprintf(
                '@var %s $%s ',
                $this->getTypeDocBlock($builder, $definition),
                $definition->getClassPropertyName()
            );
        }

        if (count($docCommentLines)) {
            $property->setDocComment($this->getDocComment($docCommentLines));
        }

        return $property;
    }

    private function generateMethodParameter(FileBuilder $builder, PropertyDefinition $definition) : Param
    {
        return $this
            ->factory
            ->param($definition->getClassPropertyName())
            ->setType($this->getTypePhp($builder, $definition));
    }

    private function getAssignmentDefinition(string $name) : Assign
    {
        return new Assign(
            new Variable('this->' . $name),
            new Variable($name)
        );
    }

    private function generateGetter(FileBuilder $builder, PropertyDefinition $definition) : Method
    {
        $getterName = $definition->getGetterName();
        if ($getterName === null) {
            throw new Exception('Getter name should be set it hasGetter is true');
        }

        $method = $this->factory
            ->method($getterName)
            ->makePublic()
            ->setReturnType($this->getTypePhp($builder, $definition))
            ->addStmt(new Return_(new Variable('this->' . $definition->getClassPropertyName())));

        if ($this->fullDocs || $definition->isArray()) {
            $method->setDocComment(
                $this->getDocComment(['@return ' . $this->getTypeDocBlock($builder, $definition)])
            );
        }

        return $method;
    }

    private function generateSetter(FileBuilder $builder, PropertyDefinition $definition) : Method
    {
        $setterName = $definition->getSetterName();
        if ($setterName === null) {
            throw new Exception('Setter name should be set it hasSetter is true');
        }

        $method = $this->factory
            ->method($setterName)
            ->makePublic()
            ->setReturnType('self')
            ->addParam($this->generateMethodParameter($builder, $definition))
            ->addStmt($this->getAssignmentDefinition($definition->getClassPropertyName()))
            ->addStmt(new Return_(new Variable('this')));

        if ($this->fullDocs || $definition->isArray()) {
            $blocks = [
                sprintf(
                    '@param %s $%s',
                    $this->getTypeDocBlock($builder, $definition),
                    $definition->getClassPropertyName()
                ),
            ];
            if ($this->fullDocs) {
                $blocks[] = '@return self';
            }

            $method->setDocComment($this->getDocComment($blocks));
        }

        return $method;
    }

    private function generateResponseCodeStaticMethod(ResponseDtoDefinition $definition) : Method
    {
        $responseCode = $definition->getStatusCode();
        $method       = $this
            ->factory
            ->method('_getResponseCode')
            ->makePublic()
            ->makeStatic()
            ->setReturnType('string')
            ->addStmt(
                new Return_(
                    new String_($responseCode)
                )
            );
        if ($this->fullDocs) {
            $method->setDocComment(
                $this->getDocComment(['@return string'])
            );
        }

        return $method;
    }

    private function generateToArray(FileBuilder $builder, DtoDefinition $definition) : Method
    {
        return $this
            ->factory
            ->method('toArray')
            ->makePublic()
            ->setReturnType('array')
            ->setDocComment($this->getDocComment(['@inheritDoc']))
            ->addStmt(
                new Return_(
                    new Array_(
                        array_map(
                            fn (PropertyDefinition $p) => $this->generateToArrayItem($builder, $p),
                            $definition->getProperties()
                        )
                    )
                )
            );
    }

    private function generateToArrayItem(FileBuilder $builder, PropertyDefinition $property) : ArrayItem
    {
        $source = new Variable('this->' . $property->getClassPropertyName());
        $value  = $this->getConverter($builder, $property, false, $source);

        return new ArrayItem($value, new String_($property->getSpecPropertyName()));
    }

    private function generateFromArray(FileBuilder $builder, DtoDefinition $definition) : Method
    {
        $source = new Variable('data');
        $dto    = new Variable('dto');

        $args    = [];
        $setters = [];

        foreach ($definition->getProperties() as $property) {
            $fetch = $this->generateFromArrayPropFetch($builder, $property, $source);

            if ($property->isInConstructor()) {
                $args[] = new Arg($fetch);
            } else {
                $setters[] = new Expression(new Assign(new PropertyFetch($dto, $property->getClassPropertyName()), $fetch));
            }
        }

        $new = new New_(new Name($builder->getReference($definition)), $args);

        $statements = [];
        if (count($setters) > 0) {
            $statements[] = new Assign($dto, $new);
            foreach ($setters as $setter) {
                $statements[] = $setter;
            }

            $statements[] = new Return_($dto);
        } else {
            $statements[] = new Return_($new);
        }

        return $this
            ->factory
            ->method('fromArray')
            ->makePublic()
            ->makeStatic()
            ->setReturnType('self')
            ->addParam(new Param_($source, null, 'array'))
            ->setDocComment($this->getDocComment(['@inheritDoc']))
            ->addStmts($statements);
    }

    private function generateFromArrayPropFetch(FileBuilder $builder, PropertyDefinition $property, Variable $sourceVar) : Expr
    {
        $source = $this->generateFromArrayGetValue($property, $sourceVar);

        return $this->getConverter($builder, $property, true, $source);
    }

    private function generateFromArrayGetValue(PropertyDefinition $property, Variable $sourceVar) : Expr
    {
        return new ArrayDimFetch($sourceVar, new String_($property->getSpecPropertyName()));
    }

    private function getConverter(FileBuilder $builder, PropertyDefinition $property, bool $deserialize, Expr $source) : Expr
    {
        $converter  = null;
        $objectType = $property->getObjectTypeDefinition();
        if ($objectType !== null) {
            if ($deserialize) {
                $converter = static fn (Expr $v) : Expr => new StaticCall(new Name($builder->getReference($objectType)), 'fromArray', [new Arg($v)]);
            } else {
                $converter = static fn (Expr $v) : Expr => new MethodCall($v, 'toArray');
            }
        }

        if ($property->isArray() && $converter !== null) {
            $converter = fn (Expr $v) : Expr => $this->factory->funcCall('array_map', [
                new ArrowFunction(
                    [
                        'static' => true,
                        'params' => [$this->factory->param('v')->getNode()],
                        'expr' => $converter(new Variable('v')),
                    ]
                ),
                $v,
            ]);
        }

        if ($property->isNullable() && $converter !== null) {
            $converter = fn (Expr $v) : Expr => new Ternary(
                new Identical($this->factory->val(null), $v),
                $this->factory->val(null),
                $converter($v)
            );
        }

        if ($converter === null) {
            return $source;
        }

        return $converter($source);
    }
}
