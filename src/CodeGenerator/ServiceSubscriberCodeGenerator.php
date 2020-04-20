<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use PhpParser\Builder\Use_;
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

class ServiceSubscriberCodeGenerator extends CodeGenerator
{
    public function generate(GraphDefinition $graphDefinition): GeneratedClass
    {
        $subscriberDefinition = $graphDefinition->getServiceSubscriber();

        $fileBuilder = $this->factory->namespace($subscriberDefinition->getNamespace());

        $fileBuilder->addStmt($this->factory->use(ContainerInterface::class));

        $classBuilder = $this
            ->factory
            ->class($subscriberDefinition->getClassName())
            ->setDocComment(sprintf(self::AUTOGENERATED_WARNING, 'class'));

        foreach ($subscriberDefinition->getImplements() as $implement) {
            $classBuilder->implement($implement->getClassName());
            $this->use($fileBuilder, $subscriberDefinition->getNamespace(), $implement);
        }
        //ToDo: move upwards
        $fileBuilder->addStmt($this->factory->use(RequestHandler::class));

        $services = [];
        foreach ($graphDefinition->getSpecifications() as $specification) {
            foreach ($specification->getOperations() as $operation) {
                $services[] = $operation->getServiceInterface()->getClassName();
                $this->use($fileBuilder, $subscriberDefinition->getNamespace(), $operation->getServiceInterface());
            }
        }
        //ToDo: full DocBlock support
        $classBuilder ->addStmts(
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
                                        static fn (string $service) : ArrayItem =>
                                        new ArrayItem(
                                            new Concat(
                                                new String_('?'),
                                                new ClassConstFetch(
                                                    new Name($service),
                                                    'class'
                                                )
                                            )
                                        ),
                                        $services
                                    )
                                )
                            )
                        ),
                    $this
                        ->factory
                        ->method('get')
                        ->makePublic()
                        ->setReturnType('?RequestHandler')
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
            $subscriberDefinition->getFilePath(),
            $subscriberDefinition->getFileName(),
            $subscriberDefinition->getNamespace(),
            $subscriberDefinition->getClassName(),
            (new Standard())->prettyPrintFile([
                new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]),
                $fileBuilder->getNode(),
            ])
        );

    }
}
