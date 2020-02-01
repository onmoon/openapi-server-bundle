<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto;

use cebe\openapi\spec\Parameter;
use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Return_;
use PhpParser\PrettyPrinter\Standard;
use function count;
use function Safe\preg_replace;

final class PhpParserRootDtoFactory implements RootDtoFactory
{
    private const PATH_PARAMETERS_PREFIX  = 'PathParametersDto';
    private const QUERY_PARAMETERS_PREFIX = 'QueryParametersDto';

    private BuilderFactory $factory;
    private NamingStrategy $namingStrategy;
    private DtoFactory $dtoFactory;
    private string $languageLevel;

    public function __construct(
        BuilderFactory $builderFactory,
        NamingStrategy $namingStrategy,
        DtoFactory $dtoFactory,
        string $languageLevel
    ) {
        $this->factory        = $builderFactory;
        $this->namingStrategy = $namingStrategy;
        $this->dtoFactory     = $dtoFactory;
        $this->languageLevel  = $languageLevel;
    }

    /**
     * @param Parameter[] $pathParameters
     * @param Parameter[] $queryParameters
     *
     * @return GeneratedClass[]
     */
    public function generateRootDto(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        ?string $requestBodyDtoNamespace = null,
        ?string $requestBodyDtoClassName = null,
        array $pathParameters = [],
        array $queryParameters = []
    ) : array {
        $generatedClasses = [];

        $fileBuilder = $this
            ->factory
            ->namespace($namespace)
            ->addStmt($this->factory->use(Dto::class));

        $classBuilder = $this
            ->factory
            ->class($className)
            ->implement('Dto')
            ->makeFinal()
            ->setDocComment('/**
                              * This class was automatically generated
                              * You should not change it manually as it will be overwritten
                              */');

        if (count($pathParameters)) {
            /** @var string $baseClassName */
            $baseClassName              = preg_replace('/Dto$/', '', $className);
            $pathParametersDtoClassName = $baseClassName . self::PATH_PARAMETERS_PREFIX;
            $pathParametersDtoFileName  = $pathParametersDtoClassName . '.php';

            $generatedClasses[] = $this->dtoFactory->generateParamDto(
                $fileDirectoryPath,
                $pathParametersDtoFileName,
                $namespace,
                $pathParametersDtoClassName,
                $pathParameters
            );

            $classBuilder
                ->addStmt(
                    $this
                        ->factory
                        ->property('pathParameters')
                        ->makePrivate()
                        ->setType($pathParametersDtoClassName)
                )
                ->addStmt(
                    $this
                        ->factory
                        ->method('getPathParameters')
                        ->makePublic()
                        ->setReturnType($pathParametersDtoClassName)
                        ->addStmt(new Return_(new Variable('this->pathParameters')))
                );
        }

        if (count($queryParameters)) {
            /** @var string $baseClassName */
            $baseClassName               = preg_replace('/Dto$/', '', $className);
            $queryParametersDtoClassName = $baseClassName . self::QUERY_PARAMETERS_PREFIX;
            $queryParametersDtoFileName  = $queryParametersDtoClassName . '.php';

            $generatedClasses[] = $this->dtoFactory->generateParamDto(
                $fileDirectoryPath,
                $queryParametersDtoFileName,
                $namespace,
                $queryParametersDtoClassName,
                $queryParameters
            );

            $classBuilder
                ->addStmt(
                    $this
                        ->factory
                        ->property('queryParameters')
                        ->makePrivate()
                        ->setType($queryParametersDtoClassName)
                )
                ->addStmt(
                    $this
                        ->factory
                        ->method('getQueryParameters')
                        ->makePublic()
                        ->setReturnType($queryParametersDtoClassName)
                        ->addStmt(new Return_(new Variable('this->queryParameters')))
                );
        }

        if ($requestBodyDtoNamespace && $requestBodyDtoClassName) {
            $classBuilder
                ->addStmt(
                    $this
                        ->factory
                        ->property('body')
                        ->makePrivate()
                        ->setType($requestBodyDtoClassName)
                )
                ->addStmt(
                    $this
                        ->factory
                        ->method('getBody')
                        ->makePublic()
                        ->setReturnType($requestBodyDtoClassName)
                        ->addStmt(new Return_(new Variable('this->body')))
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
}
