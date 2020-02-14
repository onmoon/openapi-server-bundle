<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto;

use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestParametersDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\RequestParameterDtoGenerationEvent;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Return_;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function count;
use function Safe\preg_replace;

final class PhpParserRequestDtoFactory implements RequestDtoFactory
{
    private const PATH_PARAMETERS_PREFIX  = 'PathParametersDto';
    private const QUERY_PARAMETERS_PREFIX = 'QueryParametersDto';

    private EventDispatcherInterface $eventDispatcher;
    private BuilderFactory $factory;
    private DtoFactory $dtoFactory;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        BuilderFactory $builderFactory,
        DtoFactory $dtoFactory
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->factory         = $builderFactory;
        $this->dtoFactory      = $dtoFactory;
    }

    /**
     * @return GeneratedClass[]
     *
     * @psalm-return list<GeneratedClass>
     */
    public function generateDto(RequestDtoDefinition $definition) : array
    {
        $generatedClasses = [];

        $fileBuilder = $this
            ->factory
            ->namespace($definition->namespace())
            ->addStmt($this->factory->use(Dto::class));

        $classBuilder = $this
            ->factory
            ->class($definition->className())
            ->implement('Dto')
            ->makeFinal()
            ->setDocComment('/**
                              * This class was automatically generated
                              * You should not change it manually as it will be overwritten
                              */');

        if (count($definition->pathParameters())) {
            /** @var string $baseClassName */
            $baseClassName              = preg_replace('/Dto$/', '', $definition->className());
            $pathParametersDtoClassName = $baseClassName . self::PATH_PARAMETERS_PREFIX;
            $pathParametersDtoFileName  = $pathParametersDtoClassName . '.php';

            $pathParametersDtoDefinition = new RequestParametersDtoDefinition(
                $definition->directoryPath(),
                $pathParametersDtoFileName,
                $definition->namespace(),
                $pathParametersDtoClassName,
                ...$definition->pathParameters()
            );
            $this->eventDispatcher->dispatch(new RequestParameterDtoGenerationEvent($pathParametersDtoDefinition, 'path'));
            $generatedClasses[] = $this->dtoFactory->generateRequestParametersDto($pathParametersDtoDefinition);

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

        if (count($definition->queryParameters())) {
            /** @var string $baseClassName */
            $baseClassName               = preg_replace('/Dto$/', '', $definition->className());
            $queryParametersDtoClassName = $baseClassName . self::QUERY_PARAMETERS_PREFIX;
            $queryParametersDtoFileName  = $queryParametersDtoClassName . '.php';

            $pathParametersDtoDefinition = new RequestParametersDtoDefinition(
                $definition->directoryPath(),
                $queryParametersDtoFileName,
                $definition->namespace(),
                $queryParametersDtoClassName,
                ...$definition->queryParameters()
            );

            $generatedClasses[] = $this->dtoFactory->generateRequestParametersDto($pathParametersDtoDefinition);

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

        if ($definition->requestBodyDtoDefiniton() !== null) {
            /** @psalm-var RequestBodyDtoDefinition $requestBodyDefinition */
            $requestBodyDefinition = $definition->requestBodyDtoDefiniton();
            $classBuilder
                ->addStmt(
                    $this
                        ->factory
                        ->property('body')
                        ->makePrivate()
                        ->setType($requestBodyDefinition->className())
                )
                ->addStmt(
                    $this
                        ->factory
                        ->method('getBody')
                        ->makePublic()
                        ->setReturnType($requestBodyDefinition->className())
                        ->addStmt(new Return_(new Variable('this->body')))
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
}
