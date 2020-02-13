<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto;

use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Exception;
use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\CannotCreatePropertyName;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\OpenApi\ScalarTypesResolver;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Builder\Property;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Return_;
use PhpParser\PrettyPrinter\Standard;
use function array_map;
use function array_merge;
use function count;
use function implode;
use function in_array;
use function is_array;
use function ltrim;
use function rtrim;
use function Safe\sprintf;
use function trim;
use function ucfirst;
use function version_compare;
use const PHP_EOL;

final class PhpParserDtoFactory implements DtoFactory
{
    private const DUPLICATE_NAME_CLASS_PREFIX = 'Property';

    private BuilderFactory $factory;
    private NamingStrategy $namingStrategy;
    private ScalarTypesResolver $typeResolver;

    private string $languageLevel;

    public function __construct(
        BuilderFactory $builderFactory,
        NamingStrategy $namingStrategy,
        ScalarTypesResolver $typeResolver,
        string $languageLevel
    ) {
        $this->factory        = $builderFactory;
        $this->namingStrategy = $namingStrategy;
        $this->languageLevel  = $languageLevel;
        $this->typeResolver   = $typeResolver;
    }

    /**
     * @param Parameter[] $parameters
     */
    public function generateParamDto(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        array $parameters
    ) : GeneratedClass {
        $classBuilder = $this
            ->factory
            ->class($className)
            ->implement('Dto')
            ->makeFinal()
            ->setDocComment('/**
                              * This class was automatically generated
                              * You should not change it manually as it will be overwritten
                              */');

        foreach ($parameters as $parameter) {
            if (! $this->namingStrategy->isAllowedPhpPropertyName($parameter->name)) {
                throw CannotCreatePropertyName::becauseIsNotValidPhpPropertyName($parameter->name);
            }

            if ($this->namingStrategy->isPhpReservedWord($parameter->name)) {
                throw CannotCreatePropertyName::becauseIsPhpReservedWord($parameter->name);
            }

            $type         = null;
            $iterableType = null;
            $required     = $parameter->required;

            if (! $parameter->schema instanceof Schema) {
                continue;
            }

            if (Type::isScalar($parameter->schema->type)) {
                $typeId = $this->typeResolver->findScalarType($parameter->schema);
                $type   = $this->typeResolver->getPhpType($typeId);
            } else {
                switch ($parameter->schema->type) {
                    case Type::ANY:
                        throw new Exception('\'any\' type is not supported in query and path parameters');
                    case Type::OBJECT:
                        throw new Exception('\'object\' type is not supported in query and path parameters');
                    case Type::ARRAY:
                        $type = 'array';

                        if (! ($parameter->schema->items instanceof Schema)) {
                            $iterableType = null;

                            break;
                        }

                        $iterableType = $parameter->schema->items->type;

                        if ($iterableType === Type::OBJECT) {
                            throw new Exception('\'object\' type is not supported in query and path parameters');
                        }

                        $iterableType = $this->typeResolver->getPhpType(
                            $this->typeResolver->findScalarType($parameter->schema->items)
                        );

                        break;
                    default:
                        break;
                }
            }

            if ($type === null) {
                throw new Exception('Could not determine property type');
            }

            $classBuilder
                ->addStmt(
                    $this->getPropertyDefinition(
                        $parameter->name,
                        $type,
                        ! $required,
                        $iterableType,
                        $parameter->description
                    )
                )
                ->addStmt(
                    $this->getGetterDefinition(
                        $parameter->name,
                        $type,
                        ! $required,
                        $iterableType
                    )
                );
        }

        $fileBuilder = $this
            ->factory
            ->namespace($namespace)
            ->addStmt($this->factory->use(Dto::class));

        $fileBuilder = $fileBuilder->addStmt($classBuilder);

        return new GeneratedClass(
            $fileDirectoryPath,
            $fileName,
            $namespace,
            $className,
            (new Standard())->prettyPrintFile([
                new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]),
                $fileBuilder->getNode(),
            ])
        );
    }

    /**
     * @return GeneratedClass[]
     */
    public function generateDtoClassGraph(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        bool $immutable,
        Schema $schema
    ) : array {
        $generatedClasses = [];

        $classBuilder = $this
            ->factory
            ->class($className)
            ->implement('Dto')
            ->makeFinal()
            ->setDocComment('/**
                              * This class was automatically generated
                              * You should not change it manually as it will be overwritten
                              */');

        $constructorBuilder  = $this->factory->method('__construct')->makePublic();
        $constructorRequired = false;
        $getterBuilders      = [];
        $setterBuilders      = [];
        $constructorDocBlock = [];
        $imports             = [Dto::class];

        /**
         * @var string $propertyName
         */
        foreach ($schema->properties as $propertyName => $property) {
            if (! $this->namingStrategy->isAllowedPhpPropertyName($propertyName)) {
                throw CannotCreatePropertyName::becauseIsNotValidPhpPropertyName($propertyName);
            }

            if ($this->namingStrategy->isPhpReservedWord($propertyName)) {
                throw CannotCreatePropertyName::becauseIsPhpReservedWord($propertyName);
            }

            $type         = null;
            $iterableType = null;
            /**
             * @psalm-suppress RedundantConditionGivenDocblockType
             */
            $required = is_array($schema->required) && in_array($propertyName, $schema->required);

            if ($property instanceof Reference) {
                throw new Exception('Cannot work with References');
            }

            if (Type::isScalar($property->type)) {
                $typeId = $this->typeResolver->findScalarType($property);
                $type   = $this->typeResolver->getPhpType($typeId);
            } else {
                switch ($property->type) {
                    case Type::ANY:
                        throw new Exception('\'any\' type is not supported');
                    case Type::OBJECT:
                        $generatedClassGraph = $this->generatePropertyDto(
                            $propertyName,
                            $namespace,
                            $className,
                            $fileDirectoryPath,
                            $immutable,
                            $property
                        );

                        $generatedClasses = array_merge($generatedClasses, $generatedClassGraph->getClassGraph());
                        $imports[]        = $generatedClassGraph->getImport();
                        $type             = $generatedClassGraph->getType();

                        break;
                    case Type::ARRAY:
                        $type = 'array';

                        if (! ($property->items instanceof Schema)) {
                            $iterableType = null;

                            break;
                        }

                        $iterableType = $property->items->type;

                        if ($iterableType === Type::OBJECT) {
                            $generatedClassGraph = $this->generatePropertyDto(
                                $propertyName,
                                $namespace,
                                $className,
                                $fileDirectoryPath,
                                $immutable,
                                $property->items
                            );

                            $generatedClasses = array_merge($generatedClasses, $generatedClassGraph->getClassGraph());
                            $imports[]        = $generatedClassGraph->getImport();
                            $iterableType     = $generatedClassGraph->getType();
                        } else {
                            $iterableType = $iterableType ?
                                $this->typeResolver->getPhpType(
                                    $this->typeResolver->findScalarType($property->items)
                                ) :
                                null;
                        }

                        break;
                    default:
                        break;
                }
            }

            if ($type === null) {
                continue;
            }

            $classBuilder->addStmt(
                $this->getPropertyDefinition(
                    $propertyName,
                    $type,
                    ! $required,
                    $iterableType,
                    $property->description
                )
            );

            if (! $immutable) {
                if ($required) {
                    $constructorRequired = true;

                    if ($iterableType !== null) {
                        $constructorDocBlock[] = sprintf(' * @param %s[] $%s', $iterableType, $propertyName);
                    }

                    $constructorBuilder
                        ->addParam($this->getParamDefinition($propertyName, $type, $iterableType))
                        ->addStmt($this->getAssignmentDefinition($propertyName));
                } else {
                    $setterBuilders[] = $this->getSetterDefinition($propertyName, $type, $iterableType);
                }
            }

            $getterBuilders[] = $this->getGetterDefinition($propertyName, $type, ! $required, $iterableType);
        }

        if ($constructorRequired) {
            if (count($constructorDocBlock)) {
                $constructorBuilder->setDocComment(
                    $this->getConstructorDocBlock($constructorDocBlock)
                );
            }

            $classBuilder->addStmt($constructorBuilder);
        }

        foreach ($getterBuilders as $getterBuilder) {
            $classBuilder->addStmt($getterBuilder);
        }

        foreach ($setterBuilders as $setterBuilder) {
            $classBuilder->addStmt($setterBuilder);
        }

        $fileBuilder = $this->factory->namespace($namespace);

        foreach ($imports as $import) {
            $fileBuilder->addStmt($this->factory->use(ltrim($import, '\\')));
        }

        $fileBuilder = $fileBuilder->addStmt($classBuilder);

        $generatedClasses[] = new GeneratedClass(
            $fileDirectoryPath,
            $fileName,
            $namespace,
            $className,
            (new Standard())->prettyPrintFile([
                new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]),
                $fileBuilder->getNode(),
            ])
        );

        return $generatedClasses;
    }

    private function generatePropertyDto(
        string $propertyName,
        string $parentClassNamespace,
        string $parentClassName,
        string $parentClassFileDirectoryPath,
        bool $immutable,
        Schema $schema
    ) : GeneratedPropertyDtoClassGraph {
        $propertyNamespace = $this->namingStrategy->stringToNamespace($propertyName);

        $dtoNamespace = $this->namingStrategy->buildNamespace($parentClassNamespace, $propertyNamespace);
        $dtoClassName = $this->namingStrategy->stringToNamespace($propertyNamespace . 'Dto');
        $dtoPath      = $this->namingStrategy->buildPath($parentClassFileDirectoryPath, $propertyNamespace);
        $dtoFileName  = $dtoClassName . '.php';

        if ($dtoClassName === $parentClassName) {
            $dtoClassName = self::DUPLICATE_NAME_CLASS_PREFIX . $dtoClassName;
            $dtoFileName  = self::DUPLICATE_NAME_CLASS_PREFIX . $dtoFileName;
        }

        $import = '\\' . $dtoNamespace . '\\' . $dtoClassName;
        $type   = $dtoClassName;

        return new GeneratedPropertyDtoClassGraph(
            $import,
            $type,
            $this->generateDtoClassGraph(
                $dtoPath,
                $dtoFileName,
                $dtoNamespace,
                $dtoClassName,
                $immutable,
                $schema
            )
        );
    }

    /**
     * @param string[] $lines
     *
     * @psalm-param list<string> $lines
     */
    private function getConstructorDocBlock(array $lines) : string
    {
        return implode(PHP_EOL, ['/**', ...$lines, ' */']);
    }

    private function getPropertyDefinition(
        string $name,
        string $type,
        bool $nullable = false,
        ?string $iterableType = null,
        ?string $description = null
    ) : Property {
        $property = $this->factory
            ->property($name)
            ->makePrivate();

        if ($nullable) {
            $property->setDefault(null);
        }

        $docCommentLines = [];

        if ($description) {
            $docCommentLines[] = sprintf(' %s', $description);
        }

        $nullableDocblock = $nullable ? '|null' : '';

        if (version_compare($this->languageLevel, '7.4.0') >= 0) {
            $property->setType(($nullable ? '?' : '') . ($iterableType ? 'array' : $type));
        }

        if (count($docCommentLines) > 0) {
            $docCommentLines[] = '';
        }

        if ($iterableType === null) {
            $docCommentLines[] = sprintf(' @var %s%s $%s ', $type, $nullableDocblock, $name);
        } else {
            $docCommentLines[] = sprintf(' @var %s[]%s $%s ', $iterableType, $nullableDocblock, $name);
        }

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

    private function getParamDefinition(string $name, string $type, ?string $iterableType = null) : Param
    {
        return $this
            ->factory
            ->param($name)
            ->setType($iterableType ? 'array' : $type);
    }

    private function getAssignmentDefinition(string $name) : Assign
    {
        return new Assign(new Variable('this->' . $name), new Variable($name));
    }

    private function getGetterDefinition(
        string $name,
        string $type,
        bool $nullable = false,
        ?string $iterableType = null
    ) : Method {
        $method = $this->factory
            ->method('get' . ucfirst($name))
            ->makePublic()
            ->setReturnType(($nullable ? '?' : '') . ($iterableType ? 'array' : $type))
            ->addStmt(new Return_(new Variable('this->' . $name)));

        if ($iterableType !== null) {
            $method->setDocComment(
                sprintf(
                    '/** @return %s[]%s */',
                    $iterableType,
                    $nullable ? '|null' : ''
                )
            );
        }

        return $method;
    }

    private function getSetterDefinition(
        string $name,
        string $type,
        ?string $iterableType = null
    ) : Method {
        $method = $this->factory
            ->method('set' . ucfirst($name))
            ->makePublic()
            ->setReturnType('self')
            ->addParam($this->getParamDefinition($name, $type, $iterableType))
            ->addStmt($this->getAssignmentDefinition($name))
            ->addStmt(new Return_(new Variable('this')));

        if ($iterableType !== null) {
            $method->setDocComment(
                sprintf('/** @param %s[] $%s */', $iterableType, $name)
            );
        }

        return $method;
    }
}
