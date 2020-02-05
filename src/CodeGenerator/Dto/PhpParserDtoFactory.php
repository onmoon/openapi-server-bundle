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
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
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
use function class_exists;
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

            $type         = null;
            $iterableType = null;
            $required     = $parameter->required;
            $default      = null;

            if (! $parameter->schema instanceof Schema) {
                continue;
            }

            /** @var string|int|float|bool|null $schemaDefaultValue */
            $schemaDefaultValue = $parameter->schema->default;

            $defaultValue = $schemaDefaultValue !== null && class_exists(
                $this->typeResolver->getPhpType($this->typeResolver->findScalarType($parameter->schema))
            ) ? null : $schemaDefaultValue;

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
                        $defaultValue,
                        $iterableType,
                        $parameter->description
                    )
                )
                ->addStmt(
                    $this->getGetterDefinition(
                        $parameter->name,
                        $type,
                        ! $required,
                        $defaultValue,
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
        Schema $schema,
        ?int $outputResponseCode = null,
        ?string $outputMarkerInterfaceNamespace = null,
        ?string $outputMarkerInterfaceClassName = null
    ) : array {
        $generatedClasses = [];

        $classBuilder = $this
            ->factory
            ->class($className)
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
        $imports             = [];

        if ($outputResponseCode === null) {
            $classBuilder = $classBuilder->implement('Dto');
            $imports[]    = Dto::class;
        } else {
            if ($outputMarkerInterfaceNamespace !== null && $outputMarkerInterfaceClassName !== null) {
                $classBuilder = $classBuilder->implement($outputMarkerInterfaceClassName);
                $imports[]    = $this->namingStrategy->buildNamespace(
                    $outputMarkerInterfaceNamespace,
                    $outputMarkerInterfaceClassName
                );
            } else {
                $classBuilder = $classBuilder->implement('ResponseDto');
                $imports[]    = ResponseDto::class;
            }
        }

        /**
         * @var string $propertyName
         */
        foreach ($schema->properties as $propertyName => $property) {
            if (! $this->namingStrategy->isAllowedPhpPropertyName($propertyName)) {
                throw CannotCreatePropertyName::becauseIsNotValidPhpPropertyName($propertyName);
            }

            $type         = null;
            $iterableType = null;
            $defaultValue = null;
            /**
             * @psalm-suppress RedundantConditionGivenDocblockType
             */
            $required = is_array($schema->required) && in_array($propertyName, $schema->required);

            if ($property instanceof Reference) {
                throw new Exception('Cannot work with References');
            }

            /** @var string|int|float|bool|null $schemaDefaultValue */
            $schemaDefaultValue = $property->default;

            $defaultValue = $schemaDefaultValue !== null && class_exists(
                $this->typeResolver->getPhpType($this->typeResolver->findScalarType($property))
            ) ? null : $schemaDefaultValue;

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
                    $defaultValue,
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

            $getterBuilders[] = $this->getGetterDefinition(
                $propertyName,
                $type,
                ! $required,
                $defaultValue,
                $iterableType
            );
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

        if ($outputResponseCode !== null) {
            $classBuilder
                ->addStmt(
                    $this
                        ->factory
                        ->method('_getResponseCode')
                        ->makePublic()
                        ->makeStatic()
                        ->setReturnType('int')
                        ->addStmt(
                            new Return_(
                                $this->factory->val($outputResponseCode)
                            )
                        )
                );
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

    public function generateOutputMarkerInterface(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className
    ) : GeneratedClass {
        $fileBuilder = $this
            ->factory
            ->namespace($namespace)
            ->addStmt($this->factory->use(ResponseDto::class));

        $interfaceBuilder = $this
            ->factory
            ->interface($className)
            ->extend('ResponseDto')
            ->setDocComment('/**
                              * This interface was automatically generated
                              * You should not change it manually as it will be overwritten
                              */');

        $fileBuilder = $fileBuilder->addStmt($interfaceBuilder);

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

    /**
     * @param string|int|float|bool|null $defaultValue
     */
    private function getPropertyDefinition(
        string $name,
        string $type,
        bool $nullable = false,
        $defaultValue = null,
        ?string $iterableType = null,
        ?string $description = null
    ) : Property {
        $property = $this->factory
            ->property($name)
            ->makePrivate();

        if ($defaultValue !== null) {
            $property->setDefault($defaultValue);
        } elseif ($nullable) {
            $property->setDefault(null);
        }

        $docCommentLines = [];

        if ($description) {
            $docCommentLines[] = sprintf(' %s', $description);
        }

        $nullableDocblock = $nullable && $defaultValue === null ? '|null' : '';

        if (version_compare($this->languageLevel, '7.4.0') >= 0) {
            $property->setType(($nullable && $defaultValue === null ? '?' : '') . ($iterableType ? 'array' : $type));
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

    /**
     * @param string|int|float|bool|null $defaultValue
     */
    private function getGetterDefinition(
        string $name,
        string $type,
        bool $nullable = false,
        $defaultValue = null,
        ?string $iterableType = null
    ) : Method {
        $method = $this->factory
            ->method('get' . ucfirst($name))
            ->makePublic()
            ->setReturnType(($nullable && $defaultValue === null ? '?' : '') . ($iterableType ? 'array' : $type))
            ->addStmt(new Return_(new Variable('this->' . $name)));

        if ($iterableType !== null) {
            $method->setDocComment(
                sprintf(
                    '/** @return %s[]%s */',
                    $iterableType,
                    $nullable && $defaultValue === null ? '|null' : ''
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
