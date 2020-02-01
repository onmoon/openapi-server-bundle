<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\ServiceInterface;

use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Interfaces\Service;
use PhpParser\BuilderFactory;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\PrettyPrinter\Standard;
use function sprintf;

final class PhpParserServiceInterfaceFactory implements ServiceInterfaceFactory
{
    private BuilderFactory $factory;
    private NamingStrategy $namingStrategy;
    private string $languageLevel;

    public function __construct(
        BuilderFactory $builderFactory,
        NamingStrategy $namingStrategy,
        string $languageLevel
    ) {
        $this->factory        = $builderFactory;
        $this->namingStrategy = $namingStrategy;
        $this->languageLevel  = $languageLevel;
    }

    public function generateServiceInterface(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        string $methodName,
        ?string $summary = null,
        ?string $inputDtoNamespace = null,
        ?string $inputDtoClassName = null,
        ?string $outputDtoNamespace = null,
        ?string $outputDtoClassName = null
    ) : GeneratedClass {
        $fileBuilder = $this
            ->factory
            ->namespace($namespace)
            ->addStmt($this->factory->use(Service::class));

        $interfaceBuilder = $this
            ->factory
            ->interface($className)
            ->extend('Service')
            ->setDocComment('/**
                              * This interface was automatically generated
                              * You should not change it manually as it will be overwritten
                              */');
        $methodBuilder    = $this->factory->method($methodName)->makePublic();

        if ($summary !== null) {
            $methodBuilder->setDocComment(sprintf('/** %s */', $summary));
        }

        if ($inputDtoNamespace && $inputDtoClassName) {
            $fileBuilder->addStmt(
                $this->factory->use(
                    $this->namingStrategy->buildNamespace($inputDtoNamespace, $inputDtoClassName)
                )
            );
            $methodBuilder->addParam(
                $this->factory->param('request')->setType($inputDtoClassName)
            );
        }

        if ($outputDtoNamespace && $outputDtoClassName) {
            $fileBuilder->addStmt(
                $this->factory->use(
                    $this->namingStrategy->buildNamespace($outputDtoNamespace, $outputDtoClassName)
                )
            );
            $methodBuilder->setReturnType(
                $outputDtoClassName
            );
        }

        $interfaceBuilder = $interfaceBuilder->addStmt($methodBuilder);
        $fileBuilder      = $fileBuilder->addStmt($interfaceBuilder);

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
}
