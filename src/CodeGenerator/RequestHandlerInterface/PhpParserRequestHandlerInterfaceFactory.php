<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface;

use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoMarkerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\Interfaces\Service;
use PhpParser\BuilderFactory;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\PrettyPrinter\Standard;
use function count;
use function Safe\sprintf;

final class PhpParserRequestHandlerInterfaceFactory implements RequestHandlerInterfaceFactory
{
    private BuilderFactory $factory;
    private NamingStrategy $namingStrategy;

    public function __construct(BuilderFactory $builderFactory, NamingStrategy $namingStrategy)
    {
        $this->factory        = $builderFactory;
        $this->namingStrategy = $namingStrategy;
    }

    public function generateInterface(RequestHandlerInterfaceDefinition $definition) : GeneratedClass
    {
        $fileBuilder = $this
            ->factory
            ->namespace($definition->namespace())
            ->addStmt($this->factory->use(Service::class));

        $interfaceBuilder = $this
            ->factory
            ->interface($definition->className())
            ->extend('Service')
            ->setDocComment('/**
                              * This interface was automatically generated
                              * You should not change it manually as it will be overwritten
                              */');
        $methodBuilder    = $this->factory->method($definition->methodName())->makePublic();

        if ($definition->summary() !== null) {
            $methodBuilder->setDocComment(sprintf('/** %s */', $definition->summary()));
        }

        if ($definition->requestDtoDefinition() !== null) {
            /** @psalm-var RequestDtoDefinition $requestDtoDefinition */
            $requestDtoDefinition = $definition->requestDtoDefinition();
            $fileBuilder->addStmt(
                $this->factory->use(
                    $this->namingStrategy->buildNamespace(
                        $requestDtoDefinition->namespace(),
                        $requestDtoDefinition->className()
                    )
                )
            );
            $methodBuilder->addParam(
                $this->factory->param('request')->setType(
                    $requestDtoDefinition->className()
                )
            );
        }

        $numberOfResponses = count($definition->responseDtoDefinitions());

        if ($numberOfResponses === 0) {
            $methodBuilder->setReturnType('void');
        } elseif ($numberOfResponses === 1) {
            $responseDto = $definition->responseDtoDefinitions()[0];

            $fileBuilder->addStmt(
                $this->factory->use(
                    $this->namingStrategy->buildNamespace(
                        $responseDto->namespace(),
                        $responseDto->className()
                    )
                )
            );
            $methodBuilder->setReturnType($responseDto->className());
        } elseif ($numberOfResponses > 1 &&
                  $definition->responseDtoMarkerInterfaceDefinition() !== null
        ) {
            /** @psalm-var ResponseDtoMarkerInterfaceDefinition $responseDtoMarkerInterfaceDefinition */
            $responseDtoMarkerInterfaceDefinition = $definition->responseDtoMarkerInterfaceDefinition();
            $fileBuilder->addStmt(
                $this->factory->use(
                    $this->namingStrategy->buildNamespace(
                        $responseDtoMarkerInterfaceDefinition->namespace(),
                        $responseDtoMarkerInterfaceDefinition->className()
                    )
                )
            );

            $methodBuilder
                ->setReturnType($responseDtoMarkerInterfaceDefinition->className());
        }

        $interfaceBuilder = $interfaceBuilder->addStmt($methodBuilder);
        $fileBuilder      = $fileBuilder->addStmt($interfaceBuilder);

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
}
