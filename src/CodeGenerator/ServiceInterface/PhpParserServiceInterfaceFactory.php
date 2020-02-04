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
use function count;
use function implode;
use function Safe\sprintf;

final class PhpParserServiceInterfaceFactory implements ServiceInterfaceFactory
{
    private BuilderFactory $factory;
    private NamingStrategy $namingStrategy;

    public function __construct(BuilderFactory $builderFactory, NamingStrategy $namingStrategy)
    {
        $this->factory        = $builderFactory;
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * @param string[][] $outputDtos
     *
     * @psalm-param list<array{namespace: string, className: string, code: int}> $outputDtos
     */
    public function generateServiceInterface(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        string $methodName,
        ?string $summary = null,
        ?string $inputDtoNamespace = null,
        ?string $inputDtoClassName = null,
        array $outputDtos = [],
        ?string $outputDtoMarkerInterfaceNamespace = null,
        ?string $outputDtoMarkerInterfaceClassName = null
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

        $numberOfOutputs = count($outputDtos);

        if ($numberOfOutputs === 0) {
            $methodBuilder->setReturnType('void');
        } elseif ($numberOfOutputs === 1) {
            $outputDto = $outputDtos[0];

            $fileBuilder->addStmt(
                $this->factory->use(
                    $this->namingStrategy->buildNamespace($outputDto['namespace'], $outputDto['className'])
                )
            );
            $methodBuilder->setReturnType($outputDto['className']);
        } elseif ($numberOfOutputs > 1 &&
                  $outputDtoMarkerInterfaceNamespace !== null &&
                  $outputDtoMarkerInterfaceClassName !== null
        ) {
            $fileBuilder->addStmt(
                $this->factory->use(
                    $this->namingStrategy->buildNamespace(
                        $outputDtoMarkerInterfaceNamespace,
                        $outputDtoMarkerInterfaceClassName
                    )
                )
            );

            $docCommentReturnTypes = [];

            foreach ($outputDtos as $outputDto) {
                $fileBuilder->addStmt(
                    $this->factory->use(
                        $this->namingStrategy->buildNamespace(
                            $outputDto['namespace'],
                            $outputDto['className']
                        )
                    )
                );
                $docCommentReturnTypes[] = $outputDto['className'];
            }

            $methodBuilder
                ->setReturnType($outputDtoMarkerInterfaceClassName)
                ->setDocComment('/**
                              * @return ' . implode('|', $docCommentReturnTypes) . '
                              */');
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
