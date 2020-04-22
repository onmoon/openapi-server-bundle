<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators;

use Exception;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use phpDocumentor\Reflection\Types\Null_;
use PhpParser\Builder;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Builder\Property;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use function count;
use function Safe\sprintf;

class DtoCodeGenerator extends CodeGenerator
{
    public function generate(DtoDefinition $definition) : GeneratedFileDefinition
    {
        $fileBuilder = $this
            ->factory
            ->namespace($definition->getNamespace());

        $classBuilder = $this
            ->factory
            ->class($definition->getClassName())
            ->makeFinal()
            ->setDocComment(sprintf(self::AUTOGENERATED_WARNING, 'class'));

        $implements = $definition->getImplements();
        if ($implements !== null) {
            $classBuilder->implement($implements->getClassName());
            $this->use($fileBuilder, $definition->getNamespace(), $implements);
        }

        foreach ($definition->getProperties() as $property) {
            $object = $property->getObjectTypeDefinition();
            if ($object === null) {
                continue;
            }

            $this->use($fileBuilder, $definition->getNamespace(), $object);
        }

        $classBuilder->addStmts($this->generateProperties($definition));
        $classBuilder->addStmts($this->generateConstructor($definition));
        $classBuilder->addStmts($this->generateGetters($definition));
        $classBuilder->addStmts($this->generateSetters($definition));

        if ($definition instanceof ResponseDtoDefinition) {
            $classBuilder->addStmt($this->generateResponseCodeStaticMethod($definition));
        }

        $needSerializerClass = false;
        $classBuilder->addStmt($this->generateToArray($definition, $needSerializerClass));
        if($needSerializerClass) {
            $fileBuilder->addStmt($this->factory->use(ScalarTypesResolver::SERIALIZER_FULL_CLASS));
        }

        $fileBuilder = $fileBuilder->addStmt($classBuilder);

        return new GeneratedFileDefinition(
            $definition,
            $this->printFile($fileBuilder)
        );
    }

    /**
     * @return Builder[]
     */
    private function generateProperties(DtoDefinition $definition) : array
    {
        $properties = [];
        foreach ($definition->getProperties() as $property) {
            $properties[] = $this->generateClassProperty($property);
        }

        return $properties;
    }

    /**
     * @return Builder[]
     */
    private function generateGetters(DtoDefinition $definition) : array
    {
        $properties = [];
        foreach ($definition->getProperties() as $property) {
            if (! $property->hasGetter()) {
                continue;
            }

            $properties[] = $this->generateGetter($property);
        }

        return $properties;
    }

    /**
     * @return Builder[]
     */
    private function generateSetters(DtoDefinition $definition) : array
    {
        $properties = [];
        foreach ($definition->getProperties() as $property) {
            if (! $property->hasSetter()) {
                continue;
            }

            $properties[] = $this->generateSetter($property);
        }

        return $properties;
    }

    /**
     * @return Builder[]
     */
    private function generateConstructor(DtoDefinition $definition) : array
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
                ->addParam($this->generateMethodParameter($property))
                ->addStmt($this->getAssignmentDefinition($property->getClassPropertyName()));
            if (! $this->fullDocs && ! $property->isArray()) {
                continue;
            }

            $constructorDocs[] = sprintf(
                '@param %s $%s',
                $this->getTypeDocBlock($property),
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

        $property->setType($this->getTypePhp($definition));

        $docCommentLines = [];

        if ($definition->getDescription() !== null) {
            $docCommentLines[] = sprintf('%s', $definition->getDescription());
            $docCommentLines[] = '';
        }

        $supportSymfonySerializer = true;
        /*
         * Symfony serializer does not support property class definitions.
         * ToDo: Remove this hack and phpstan ignores after serializer is no longer used.
         */

        /** @phpstan-ignore-next-line */
        if ($this->fullDocs || $definition->isArray() || $supportSymfonySerializer) {
            $docCommentLines[] = sprintf(
                '@var %s $%s ',
                $this->getTypeDocBlock($definition),
                $definition->getClassPropertyName()
            );
        }

        /** @phpstan-ignore-next-line */
        if (count($docCommentLines)) {
            $property->setDocComment($this->getDocComment($docCommentLines));
        }

        return $property;
    }

    private function generateMethodParameter(PropertyDefinition $definition) : Param
    {
        return $this
            ->factory
            ->param($definition->getClassPropertyName())
            ->setType($this->getTypePhp($definition));
    }

    private function getAssignmentDefinition(string $name) : Assign
    {
        return new Assign(
            new Variable('this->' . $name),
            new Variable($name)
        );
    }

    private function generateGetter(PropertyDefinition $definition) : Method
    {
        $getterName = $definition->getGetterName();
        if ($getterName === null) {
            throw new Exception('Getter name should be set it hasGetter is true');
        }

        $method = $this->factory
            ->method($getterName)
            ->makePublic()
            ->setReturnType($this->getTypePhp($definition))
            ->addStmt(new Return_(new Variable('this->' . $definition->getClassPropertyName())));

        if ($this->fullDocs || $definition->isArray()) {
            $method->setDocComment(
                $this->getDocComment(['@return ' . $this->getTypeDocBlock($definition)])
            );
        }

        return $method;
    }

    private function generateSetter(PropertyDefinition $definition) : Method
    {
        $setterName = $definition->getSetterName();
        if ($setterName === null) {
            throw new Exception('Setter name should be set it hasSetter is true');
        }

        $method = $this->factory
            ->method($setterName)
            ->makePublic()
            ->setReturnType('self')
            ->addParam($this->generateMethodParameter($definition))
            ->addStmt($this->getAssignmentDefinition($definition->getClassPropertyName()))
            ->addStmt(new Return_(new Variable('this')));

        if ($this->fullDocs || $definition->isArray()) {
            $blocks = [
                sprintf(
                    '@param %s $%s',
                    $this->getTypeDocBlock($definition),
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
        $responseCode = (int) $definition->getStatusCode();
        $method       = $this
            ->factory
            ->method('_getResponseCode')
            ->makePublic()
            ->makeStatic()
            ->setReturnType('?int')
            ->addStmt(
                new Return_(
                    $this->factory->val($responseCode !== 0 ? $responseCode : null)
                )
            );
        if ($this->fullDocs) {
            $method->setDocComment(
                $this->getDocComment(['@return ?int'])
            );
        }

        return $method;
    }

    private function generateToArray(DtoDefinition $definition, bool &$needSerializerClass) : Method
    {
        $method = $this
            ->factory
            ->method('toArray')
            ->makePublic()
            ->setReturnType('array')
            ->addStmt(
                new Return_(
                    new Array_(
                        array_map(
                            function ($p) use (&$needSerializerClass) {
                                return $this->generateToArrayItem($p, $needSerializerClass);
                            },
                            $definition->getProperties()
                        )
                    )
                )
            );

        if ($this->fullDocs) {
            $method->setDocComment(
                $this->getDocComment(['@return array'])
            );
        }

        return $method;
    }

    private function generateToArrayItem(PropertyDefinition $property, bool &$needSerializerClass) : ArrayItem {
        $source = new Variable('this->' . $property->getClassPropertyName());

        $serializer = null;
        if ($property->getObjectTypeDefinition() !== null) {
            $serializer = fn ($v) => new MethodCall($v, 'toArray');
        } elseif($property->getScalarTypeId() !== null) {
            $serializerFn = $this->typeResolver->getSerializer($property->getScalarTypeId());
            if($serializerFn !== null) {
                $serializer = fn ($v) => new StaticCall(
                    new Name(ScalarTypesResolver::SERIALIZER_CLASS),
                    $serializerFn,
                    [new Arg($v)]
                );
                $needSerializerClass = true;
            }
        }

        if($property->isArray() && $serializer !== null) {
            $serializer = fn ($v) => new FuncCall(
                new Name('array_map'),
                [
                    new ArrowFunction(
                        [
                            'static' => true,
                            'params' => [$this->factory->param('v')->getNode()],
                            'expr' => $serializer(new Variable('v'))
                        ]
                    ),
                    $v
                ]
            );
        }

        if($property->isNullable() && $serializer !== null) {
            $serializer = fn ($v) => new Ternary(
                new Identical($this->factory->val(null), $v),
                $this->factory->val(null),
                $serializer($v)
            );
        }

        return new ArrayItem(
            $serializer !== null ? $serializer($source) : $source,
            new String_($property->getSpecPropertyName())
        );

    }
}
