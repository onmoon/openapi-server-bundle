<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber;

use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\Interfaces\Service;
use PhpParser\Builder\Use_;
use PhpParser\BuilderFactory;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\PrettyPrinter\Standard;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use function array_map;
use function array_merge;

final class PhpParserServiceSubscriberFactory implements ServiceSubscriberFactory
{
    private BuilderFactory $factory;
    private NamingStrategy $namingStrategy;

    public function __construct(BuilderFactory $builderFactory, NamingStrategy $namingStrategy)
    {
        $this->factory        = $builderFactory;
        $this->namingStrategy = $namingStrategy;
    }

    public function generateServiceSubscriber(ServiceSubscriberDefinition $definition) : GeneratedClass
    {
        $fileBuilder = $this->factory->namespace($definition->namespace());

        $fileBuilder->addStmts(
            array_merge(
                [
                    $this->factory->use(ContainerInterface::class),
                    $this->factory->use(ServiceSubscriberInterface::class),
                    $this->factory->use(ApiLoader::class),
                    $this->factory->use(Service::class),
                ],
                array_map(
                    fn (RequestHandlerInterfaceDefinition $interfaceDefinition) : Use_
                        => $this->factory->use(
                            $this->namingStrategy->buildNamespace(
                                $interfaceDefinition->namespace(),
                                $interfaceDefinition->className()
                            )
                        ),
                    $definition->requestHandlerInterfaces()
                )
            )
        );

        $classBuilder = $this
            ->factory
            ->class($definition->className())
            ->implement('ServiceSubscriberInterface')
            ->implement('ApiLoader')
            ->setDocComment('/**
                              * This class was automatically generated
                              * You should not change it manually as it will be overwritten
                              */')
            ->addStmts(
                [
                    $this
                        ->factory
                        ->property('locator')
                        ->makePrivate()
                        ->setType('ContainerInterface'),
                    $this
                        ->factory
                        ->method('__construct')
                        ->makePublic()
                        ->addParam(
                            $this->factory->param('locator')->setType('ContainerInterface')
                        )
                        ->addStmt(
                            new Assign(new Variable('this->locator'), new Variable('locator'))
                        ),
                    $this
                        ->factory
                        ->method('getSubscribedServices')
                        ->makePublic()
                        ->makeStatic()
                        ->setDocComment('/**
                                         * @inheritDoc
                                         */')
                        ->addStmt(
                            new Return_(
                                new Array_(
                                    array_map(
                                        static fn (RequestHandlerInterfaceDefinition $interfaceDefinition) : ArrayItem =>
                                            new ArrayItem(
                                                new Concat(
                                                    new String_('?'),
                                                    new ClassConstFetch(
                                                        new Name($interfaceDefinition->className()),
                                                        'class'
                                                    )
                                                )
                                            ),
                                        $definition->requestHandlerInterfaces()
                                    )
                                )
                            )
                        ),
                    $this
                        ->factory
                        ->method('get')
                        ->makePublic()
                        ->setReturnType('?Service')
                        ->addParam(
                            $this->factory->param('interface')->setType('string')
                        )
                        ->addStmt(
                            new If_(new BooleanNot(new MethodCall(
                                new Variable('this->locator'),
                                'has',
                                [
                                    new Arg(
                                        new Variable('interface')
                                    ),
                                ]
                            )), ['stmts' => [new Return_($this->factory->val(null))]])
                        )
                        ->addStmt(
                            new Return_(
                                new MethodCall(
                                    new Variable('this->locator'),
                                    'get',
                                    [
                                        new Arg(
                                            new Variable('interface')
                                        ),
                                    ]
                                )
                            )
                        ),
                ]
            );

        $fileBuilder->addStmt($classBuilder);

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
