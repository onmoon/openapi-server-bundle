<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Exception;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ClassPropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\GetterMethodDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\MethodParameterDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\PropertyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestParametersDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoMarkerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SchemaBasedDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SetterMethodDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\CannotCreatePropertyName;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ClassPropertyGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ConstructorParameterGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\GetterMethodGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\PropertyDtoGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\SetterMethodGenerationEvent;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
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
    private EventDispatcherInterface $eventDispatcher;
    private string $languageLevel;

    public function __construct(
        BuilderFactory $builderFactory,
        NamingStrategy $namingStrategy,
        ScalarTypesResolver $typeResolver,
        EventDispatcherInterface $eventDispatcher,
        string $languageLevel
    ) {
        $this->factory         = $builderFactory;
        $this->namingStrategy  = $namingStrategy;
        $this->typeResolver    = $typeResolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->languageLevel   = $languageLevel;
    }

    public function generateRequestParametersDto(RequestParametersDtoDefinition $definition) : GeneratedClass
    {
        $classBuilder = $this
            ->factory
            ->class($definition->className())
            ->implement('Dto')
            ->makeFinal()
            ->setDocComment('/**
                              * This class was automatically generated
                              * You should not change it manually as it will be overwritten
                              */');

        foreach ($definition->parameters() as $parameter) {
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

            $propertyDefinition = new ClassPropertyDefinition($parameter->name, $type);
            $required ? $propertyDefinition->makeNotNullable() : $propertyDefinition->makeNullable();
            $propertyDefinition->setDefaultValue($defaultValue);
            $propertyDefinition->setIterableType($iterableType);
            $propertyDefinition->setDescription($parameter->description);

            $this->eventDispatcher->dispatch(new ClassPropertyGenerationEvent($definition, $propertyDefinition));

            $getterDefinition = new GetterMethodDefinition($parameter->name, $type);
            $required ? $getterDefinition->makeNotNullable() : $getterDefinition->makeNullable();
            $getterDefinition->setDefaultValue($defaultValue);
            $getterDefinition->setIterableType($iterableType);

            $this->eventDispatcher->dispatch(new GetterMethodGenerationEvent($definition, $getterDefinition));

            $classBuilder
                ->addStmt($this->generateClassProperty($propertyDefinition))
                ->addStmt($this->generateGetter($getterDefinition));
        }

        $fileBuilder = $this
            ->factory
            ->namespace($definition->namespace())
            ->addStmt($this->factory->use(Dto::class));

        $fileBuilder = $fileBuilder->addStmt($classBuilder);

        return new GeneratedClass(
            $definition->directoryPath(),
            $definition->fileName(),
            $definition->namespace(),
            $definition->className(),
            (new Standard())->prettyPrintFile([
                new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]),
                $fileBuilder->getNode(),
            ])
        );
    }

    /**
     * @return GeneratedClass[]
     *
     * @psalm-return array<array-key, GeneratedClass>
     */
    public function generateDtoClassGraph(SchemaBasedDtoDefinition $definition) : array
    {
        /**
         * @var GeneratedClass[] $generatedClasses
         */
        $generatedClasses = [];

        $classBuilder = $this
            ->factory
            ->class($definition->className())
            ->makeFinal()
            ->setDocComment('/**
                              * This class was automatically generated
                              * You should not change it manually as it will be overwritten
                              */');

        $constructorBuilder   = $this->factory->method('__construct')->makePublic();
        $constructorRequired  = false;
        $getterBuilders       = [];
        $setterBuilders       = [];
        $constructorDocBlock  = [];
        $imports              = [];
        $baseInterfaceDefined = false;

        if ($definition instanceof ResponseDtoDefinition) {
            if ($definition->markerInterfaceDefintion() instanceof ResponseDtoMarkerInterfaceDefinition) {
                /** @psalm-var ResponseDtoMarkerInterfaceDefinition $markerInterfaceDefintion */
                $markerInterfaceDefintion = $definition->markerInterfaceDefintion();
                $classBuilder             = $classBuilder->implement($markerInterfaceDefintion->namespace());
                $imports[]                = $this->namingStrategy->buildNamespace(
                    $markerInterfaceDefintion->namespace(),
                    $markerInterfaceDefintion->className()
                );
                $baseInterfaceDefined     = true;
            } elseif ($definition->responseCode() !== null) {
                $classBuilder         = $classBuilder->implement('ResponseDto');
                $imports[]            = ResponseDto::class;
                $baseInterfaceDefined = true;
            }
        }

        if (! $baseInterfaceDefined) {
            $classBuilder = $classBuilder->implement('Dto');
            $imports[]    = Dto::class;
        }

        /**
         * @var string $propertyName
         */
        foreach ($definition->schema()->properties as $propertyName => $property) {
            if (! $this->namingStrategy->isAllowedPhpPropertyName($propertyName)) {
                throw CannotCreatePropertyName::becauseIsNotValidPhpPropertyName($propertyName);
            }

            $type         = null;
            $iterableType = null;
            $defaultValue = null;
            /**
             * @psalm-suppress RedundantConditionGivenDocblockType
             */
            $required = is_array($definition->schema()->required) &&
                        in_array($propertyName, $definition->schema()->required);

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
                        $propertyDtoDefinition = new PropertyDtoDefinition(
                            $definition->directoryPath(),
                            $definition->fileName(),
                            $definition->namespace(),
                            $definition->className(),
                            $property,
                            $propertyName
                        );
                        $definition->isImmutable() ?
                            $propertyDtoDefinition->makeImmutable() :
                            $propertyDtoDefinition->makeMutable();

                        $this->eventDispatcher->dispatch(new PropertyDtoGenerationEvent($propertyDtoDefinition));
                        $generatedClassGraph = $this->generatePropertyDto($propertyDtoDefinition);

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
                            $propertyDtoDefinition = new PropertyDtoDefinition(
                                $definition->directoryPath(),
                                $definition->fileName(),
                                $definition->namespace(),
                                $definition->className(),
                                $property->items,
                                $propertyName
                            );
                            $definition->isImmutable() ?
                                $propertyDtoDefinition->makeImmutable() :
                                $propertyDtoDefinition->makeMutable();

                            $this->eventDispatcher->dispatch(new PropertyDtoGenerationEvent($propertyDtoDefinition));
                            $generatedClassGraph = $this->generatePropertyDto($propertyDtoDefinition);

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

            $propertyDefinition = new ClassPropertyDefinition($propertyName, $type);
            $required ? $propertyDefinition->makeNotNullable() : $propertyDefinition->makeNullable();
            $propertyDefinition->setDefaultValue($defaultValue);
            $propertyDefinition->setIterableType($iterableType);
            $propertyDefinition->setDescription($property->description);

            $this->eventDispatcher->dispatch(new ClassPropertyGenerationEvent($definition, $propertyDefinition));
            $classBuilder->addStmt($this->generateClassProperty($propertyDefinition));

            if (! $definition->isImmutable()) {
                if ($required) {
                    $constructorRequired = true;

                    if ($iterableType !== null) {
                        $constructorDocBlock[] = sprintf(' * @param %s[] $%s', $iterableType, $propertyName);
                    }

                    $constructorParameterDefinition = new MethodParameterDefinition($propertyName, $type);
                    $constructorParameterDefinition->setIterableType($iterableType);

                    $this->eventDispatcher->dispatch(
                        new ConstructorParameterGenerationEvent($definition, $constructorParameterDefinition)
                    );

                    $constructorBuilder
                        ->addParam($this->generateMethodParameter($constructorParameterDefinition))
                        ->addStmt($this->getAssignmentDefinition($propertyName));
                } else {
                    $setterDefinition = new SetterMethodDefinition($propertyName, $type);
                    $setterDefinition->setIterableType($iterableType);

                    $this->eventDispatcher->dispatch(new SetterMethodGenerationEvent($definition, $setterDefinition));

                    $setterBuilders[] = $this->generateSetter($setterDefinition);
                }
            }

            $getterDefinition = new GetterMethodDefinition($propertyName, $type);
            $required ? $getterDefinition->makeNotNullable() : $getterDefinition->makeNullable();
            $getterDefinition->setDefaultValue($defaultValue);
            $getterDefinition->setIterableType($iterableType);

            $this->eventDispatcher->dispatch(new GetterMethodGenerationEvent($definition, $getterDefinition));

            $getterBuilders[] = $this->generateGetter($getterDefinition);
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

        $fileBuilder = $this->factory->namespace($definition->namespace());

        foreach ($imports as $import) {
            $fileBuilder->addStmt($this->factory->use(ltrim($import, '\\')));
        }

        if ($definition instanceof ResponseDtoDefinition && $definition->responseCode() !== null) {
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
                                $this->factory->val($definition->responseCode())
                            )
                        )
                );
        }

        $fileBuilder = $fileBuilder->addStmt($classBuilder);

        $generatedClasses[] = new GeneratedClass(
            $definition->directoryPath(),
            $definition->fileName(),
            $definition->namespace(),
            $definition->className(),
            (new Standard())->prettyPrintFile([
                new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]),
                $fileBuilder->getNode(),
            ])
        );

        return $generatedClasses;
    }

    public function generateResponseMarkerInterface(ResponseDtoMarkerInterfaceDefinition $definition) : GeneratedClass
    {
        $fileBuilder = $this
            ->factory
            ->namespace($definition->namespace())
            ->addStmt($this->factory->use(ResponseDto::class));

        $interfaceBuilder = $this
            ->factory
            ->interface($definition->className())
            ->extend('ResponseDto')
            ->setDocComment('/**
                              * This interface was automatically generated
                              * You should not change it manually as it will be overwritten
                              */');

        $fileBuilder = $fileBuilder->addStmt($interfaceBuilder);

        return new GeneratedClass(
            $definition->directoryPath(),
            $definition->fileName(),
            $definition->namespace(),
            $definition->className(),
            (new Standard())->prettyPrintFile([
                new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]),
                $fileBuilder->getNode(),
            ])
        );
    }

    private function generatePropertyDto(PropertyDtoDefinition $definition) : GeneratedPropertyDtoClassGraph
    {
        $propertyNamespace = $this->namingStrategy->stringToNamespace($definition->propertyName());

        $dtoNamespace = $this->namingStrategy->buildNamespace($definition->namespace(), $propertyNamespace);
        $dtoClassName = $this->namingStrategy->stringToNamespace($propertyNamespace . 'Dto');
        $dtoPath      = $this->namingStrategy->buildPath($definition->directoryPath(), $propertyNamespace);
        $dtoFileName  = $dtoClassName . '.php';

        if ($dtoClassName === $definition->className()) {
            $dtoClassName = self::DUPLICATE_NAME_CLASS_PREFIX . $dtoClassName;
            $dtoFileName  = self::DUPLICATE_NAME_CLASS_PREFIX . $dtoFileName;
        }

        $import = '\\' . $dtoNamespace . '\\' . $dtoClassName;
        $type   = $dtoClassName;

        $propertyDtoDefinition = new SchemaBasedDtoDefinition(
            $dtoPath,
            $dtoFileName,
            $dtoNamespace,
            $dtoClassName,
            $definition->schema()
        );
        $definition->isImmutable() ? $propertyDtoDefinition->makeImmutable() : $propertyDtoDefinition->makeMutable();

        return new GeneratedPropertyDtoClassGraph($import, $type, $this->generateDtoClassGraph($propertyDtoDefinition));
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

    private function generateClassProperty(ClassPropertyDefinition $definition) : Property
    {
        $property = $this->factory
            ->property($definition->name())
            ->makePrivate();

        if ($definition->defaultValue() !== null) {
            $property->setDefault($definition->defaultValue());
        } elseif ($definition->isNullable()) {
            $property->setDefault(null);
        }

        $docCommentLines = [];

        if ($definition->description() !== null) {
            $docCommentLines[] = sprintf(' %s', $definition->description());
        }

        $nullableDocblock = $definition->isNullable() && $definition->defaultValue() === null ? '|null' : '';

        if (version_compare($this->languageLevel, '7.4.0') >= 0) {
            $property->setType(
                ($definition->isNullable() && $definition->defaultValue() === null ? '?' : '') .
                ($definition->iterableType() !== null ? 'array' : $definition->type())
            );
        }

        if (count($docCommentLines) > 0) {
            $docCommentLines[] = '';
        }

        if ($definition->iterableType() === null) {
            $docCommentLines[] = sprintf(
                ' @var %s%s $%s ',
                $definition->type(),
                $nullableDocblock,
                $definition->name()
            );
        } else {
            $docCommentLines[] = sprintf(
                ' @var %s[]%s $%s ',
                $definition->iterableType(),
                $nullableDocblock,
                $definition->name()
            );
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

    private function generateMethodParameter(MethodParameterDefinition $definition) : Param
    {
        return $this
            ->factory
            ->param($this->namingStrategy->stringToMethodName($definition->name()))
            ->setType($definition->iterableType() !== null ? 'array' : $definition->type());
    }

    private function getAssignmentDefinition(string $name) : Assign
    {
        return new Assign(
            new Variable('this->' . $name),
            new Variable($this->namingStrategy->stringToMethodName($name))
        );
    }

    private function generateGetter(GetterMethodDefinition $definition) : Method
    {
        $method = $this->factory
            ->method('get' . ucfirst($this->namingStrategy->stringToMethodName($definition->name())))
            ->makePublic()
            ->setReturnType(
                ($definition->isNullable() && $definition->defaultValue() === null ? '?' : '') .
                ($definition->iterableType() !== null ? 'array' : $definition->type())
            )
            ->addStmt(new Return_(new Variable('this->' . $definition->name())));

        if ($definition->iterableType() !== null) {
            $method->setDocComment(
                sprintf(
                    '/** @return %s[]%s */',
                    $definition->iterableType(),
                    $definition->isNullable() && $definition->defaultValue() === null ? '|null' : ''
                )
            );
        }

        return $method;
    }

    private function generateSetter(SetterMethodDefinition $definition) : Method
    {
        $methodParameterDefinition = new MethodParameterDefinition($definition->name(), $definition->type());
        $methodParameterDefinition->setIterableType($definition->iterableType());
        $method = $this->factory
            ->method('set' . ucfirst($this->namingStrategy->stringToMethodName($definition->name())))
            ->makePublic()
            ->setReturnType('self')
            ->addParam($this->generateMethodParameter($methodParameterDefinition))
            ->addStmt($this->getAssignmentDefinition($definition->name()))
            ->addStmt(new Return_(new Variable('this')));

        if ($definition->iterableType() !== null) {
            $method->setDocComment(
                sprintf('/** @param %s[] $%s */', $definition->iterableType(), $definition->name())
            );
        }

        return $method;
    }
}
