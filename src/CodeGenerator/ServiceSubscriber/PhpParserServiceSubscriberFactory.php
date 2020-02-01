<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber;

use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
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

    /**
     * @param GeneratedClass[] $serviceInterfaces
     */
    public function generateServiceSubscriber(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        array $serviceInterfaces
    ) : GeneratedClass {
        $fileBuilder = $this->factory->namespace($namespace);

        $fileBuilder->addStmts(
            array_merge(
                [
                    $this->factory->use('Psr\Container\ContainerInterface'),
                    $this->factory->use('Symfony\Contracts\Service\ServiceSubscriberInterface'),
                    $this->factory->use('OnMoon\OpenApiServerBundle\Interfaces\ApiLoader'),
                ],
                array_map(
                    fn (GeneratedClass $generatedClass) : Use_ => $this->factory->use($generatedClass->getFQCN()),
                    $serviceInterfaces
                )
            )
        );

        $classBuilder = $this
            ->factory
            ->class($className)
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
                                        static fn (GeneratedClass $generatedClass) : ArrayItem =>
                                            new ArrayItem(
                                                new Concat(
                                                    new String_('?'),
                                                    new ClassConstFetch(
                                                        new Name($generatedClass->getClassName()),
                                                        'class'
                                                    )
                                                )
                                            ),
                                        $serviceInterfaces
                                    )
                                )
                            )
                        ),
                    $this
                        ->factory
                        ->method('get')
                        ->makePublic()
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
