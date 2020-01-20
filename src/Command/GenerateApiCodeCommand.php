<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Command;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\DtoFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\RootDtoFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FileWriter;
use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceInterface\ServiceInterfaceFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\ServiceSubscriberFactory;
use OnMoon\OpenApiServerBundle\Exception\CannotGenerateCodeForOperation;
use OnMoon\OpenApiServerBundle\Router\RouteLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;
use function array_filter;
use function array_key_exists;
use function array_merge;

class GenerateApiCodeCommand extends Command
{
    public const APIS_NAMESPACE                = 'Apis';
    public const SERVICE_SUFFIX                = 'ServiceInterface';
    private const DTO_NAMESPACE                = 'Dto';
    private const SERVICE_SUBSCRIBER_NAMESPACE = 'ServiceSubscriber';
    private const SERVICE_SUBSCRIBER_CLASSNAME = 'ApiServiceLoaderServiceSubscriber';
    private const REQUEST_SUFFIX               = 'Request';
    private const REQUEST_BODY_SUFFIX          = 'RequestBody';
    private const RESPONSE_SUFFIX              = 'Response';
    private const DTO_SUFFIX                   = 'Dto';

    private NamingStrategy $namingStrategy;
    private RootDtoFactory $rootDtoFactory;
    private DtoFactory $dtoFactory;
    private ServiceInterfaceFactory $serviceInterfaceFactory;
    private ServiceSubscriberFactory $serviceSubscriberFactory;
    private FileWriter $fileWriter;
    private RouterInterface $router;
    private string $rootNamespace;
    private string $rootPath;
    private string $mediaType;

    /**
     * @param NamingStrategy $namingStrategy
     * @param RootDtoFactory $rootDtoFactory
     * @param DtoFactory $dtoFactory
     * @param ServiceInterfaceFactory $serviceInterfaceFactory
     * @param ServiceSubscriberFactory $serviceSubscriberFactory
     * @param FileWriter $fileWriter
     * @param RouterInterface $router
     * @param string $rootNamespace
     * @param string $rootPath
     * @param string $mediaType
     * @param string|null $name
     */
    public function __construct(
        NamingStrategy $namingStrategy,
        RootDtoFactory $rootDtoFactory,
        DtoFactory $dtoFactory,
        ServiceInterfaceFactory $serviceInterfaceFactory,
        ServiceSubscriberFactory $serviceSubscriberFactory,
        FileWriter $fileWriter,
        RouterInterface $router,
        string $rootNamespace,
        string $rootPath,
        string $mediaType,
        ?string $name = null
    ) {
        $this->namingStrategy           = $namingStrategy;
        $this->rootDtoFactory           = $rootDtoFactory;
        $this->dtoFactory               = $dtoFactory;
        $this->serviceInterfaceFactory  = $serviceInterfaceFactory;
        $this->serviceSubscriberFactory = $serviceSubscriberFactory;
        $this->fileWriter               = $fileWriter;
        $this->router                   = $router;
        $this->rootNamespace            = $rootNamespace;
        $this->rootPath                 = $rootPath;
        $this->mediaType                = $mediaType;

        parent::__construct($name);
    }

    /** @var string */
    protected static $defaultName = 'app:openapi-generate-code';

    protected function execute(InputInterface $input, OutputInterface $output) : ?int
    {
        /** @var GeneratedClass[] $filesToGenerate */
        $filesToGenerate = [];
        /** @var GeneratedClass[] $serviceInterfaces */
        $serviceInterfaces = [];

        foreach ($this->findSpecifications() as $specificationFile=>$parsedSpecification) {
            /** @var OpenApi $parsedSpecification */

            if ($parsedSpecification->paths === null) {
                continue;
            }

            $apiName      = $this->namingStrategy->stringToNamespace($parsedSpecification->info->title);
            $apiNamespace = $this->namingStrategy->buildNamespace($this->rootNamespace, self::APIS_NAMESPACE, $apiName);
            $apiPath      = $this->namingStrategy->buildPath($this->rootPath, self::APIS_NAMESPACE, $apiName);

            foreach ($parsedSpecification->paths as $url => $pathItem) {
                foreach ($pathItem->getOperations() as $method => $operation) {
                    $operationId = $operation->operationId;
                    $summary     = $operation->summary;

                    $operationName      = $this->namingStrategy->stringToNamespace($operationId);
                    $operationNamesapce = $this->namingStrategy->buildNamespace($apiNamespace, $operationName);
                    $operationPath      = $this->namingStrategy->buildPath($apiPath, $operationName);

                    if ($operationId === null) {
                        throw CannotGenerateCodeForOperation::becauseNoOperationIdSpecified(
                            $url,
                            $method,
                            $specificationFile->getFilePath()
                        );
                    }

                    $rootDtoNamespace   = null;
                    $rootDtoClassName   = null;
                    $inputDtoNamespace  = null;
                    $inputDtoClassName  = null;
                    $outputDtoNamespace = null;
                    $outputDtoClassName = null;

                    $requestBody = $operation->requestBody;
                    $responses   = $operation->responses;

                    if ($requestBody !== null &&
                        $requestBody->content !== null &&
                        array_key_exists($this->mediaType, $requestBody->content)
                    ) {
                        $mediaType = $requestBody->content[$this->mediaType];

                        if ($mediaType->schema instanceof Schema) {
                            $schema = $mediaType->schema;

                            $dtoNamespace = $this->namingStrategy->buildNamespace(
                                $operationNamesapce,
                                self::DTO_NAMESPACE,
                                self::REQUEST_SUFFIX
                            );
                            $dtoClassName = $this->namingStrategy->stringToNamespace(
                                $operationName . self::REQUEST_BODY_SUFFIX . self::DTO_SUFFIX
                            );
                            $dtoPath      = $this->namingStrategy->buildPath(
                                $operationPath, self::DTO_NAMESPACE, self::REQUEST_SUFFIX
                            );
                            $dtoFileName  = $dtoClassName . '.php';

                            $inputDtoNamespace = $dtoNamespace;
                            $inputDtoClassName = $dtoClassName;

                            /** @var GeneratedClass[] $filesToGenerate */
                            $filesToGenerate = array_merge(
                                $filesToGenerate,
                                $this->generateDtoGraph(
                                    $schema,
                                    $dtoPath,
                                    $dtoFileName,
                                    $dtoNamespace,
                                    $dtoClassName,
                                    true
                                )
                            );
                        }
                    }

                    if ($responses !== null &&
                        $responses[200] !== null &&
                        $responses[200]->content !== null &&
                        array_key_exists($this->mediaType, $responses[200]->content)
                    ) {
                        $mediaType = $responses[200]->content[$this->mediaType];

                        if ($mediaType->schema instanceof Schema) {
                            $schema = $mediaType->schema;

                            $dtoNamespace = $this->namingStrategy->buildNamespace(
                                $operationNamesapce,
                                self::DTO_NAMESPACE,
                                self::RESPONSE_SUFFIX
                            );
                            $dtoClassName = $this->namingStrategy->stringToNamespace(
                                $operationName . self::RESPONSE_SUFFIX . self::DTO_SUFFIX
                            );
                            $dtoPath      = $this->namingStrategy->buildPath(
                                $operationPath, self::DTO_NAMESPACE, self::RESPONSE_SUFFIX
                            );
                            $dtoFileName  = $dtoClassName . '.php';

                            $outputDtoNamespace = $dtoNamespace;
                            $outputDtoClassName = $dtoClassName;

                            /** @var GeneratedClass[] $filesToGenerate */
                            $filesToGenerate = array_merge(
                                $filesToGenerate,
                                $this->generateDtoGraph(
                                    $schema,
                                    $dtoPath,
                                    $dtoFileName,
                                    $dtoNamespace,
                                    $dtoClassName,
                                    false
                                )
                            );
                        }
                    }

                    // Root dto generation

                    $dtoNamespace = $this->namingStrategy->buildNamespace(
                        $operationNamesapce,
                        self::DTO_NAMESPACE,
                        self::REQUEST_SUFFIX
                    );
                    $dtoClassName = $this->namingStrategy->stringToNamespace(
                        $operationName . self::REQUEST_SUFFIX . self::DTO_SUFFIX
                    );
                    $dtoPath      = $this->namingStrategy->buildPath(
                        $operationPath, self::DTO_NAMESPACE, self::REQUEST_SUFFIX
                    );
                    $dtoFileName  = $dtoClassName . '.php';

                    $rootDtoNamespace = $dtoNamespace;
                    $rootDtoClassName = $dtoClassName;

                    $parameters = array_merge($pathItem->parameters, $operation->parameters);

                    $filesToGenerate = array_merge(
                        $filesToGenerate,
                        $this->rootDtoFactory->generateRootDto(
                            $dtoPath,
                            $dtoFileName,
                            $dtoNamespace,
                            $dtoClassName,
                            $inputDtoNamespace,
                            $inputDtoClassName,
                            $this->filterAllowedParameters($parameters, 'path'),
                            $this->filterAllowedParameters($parameters, 'query')
                        )
                    );

                    // Service interface generation

                    $operationName             = $this->namingStrategy->stringToNamespace($operationId);
                    $serviceInterfaceNamesapce = $this->namingStrategy->buildNamespace($apiNamespace, $operationName);
                    $serviceInterfacePath      = $this->namingStrategy->buildPath($apiPath, $operationName);

                    $serviceInterfaceClassName = $this->namingStrategy->stringToNamespace(
                        $operationName . self::SERVICE_SUFFIX
                    );
                    $serviceInterfaceMethod    = $this->namingStrategy->stringToMethodName($operationId);
                    $serviceInterfaceFileName  = $serviceInterfaceClassName . '.php';

                    $serviceInterface = $this->serviceInterfaceFactory->generateServiceInterface(
                        $serviceInterfacePath,
                        $serviceInterfaceFileName,
                        $serviceInterfaceNamesapce,
                        $serviceInterfaceClassName,
                        $serviceInterfaceMethod,
                        $summary,
                        $rootDtoNamespace,
                        $rootDtoClassName,
                        $outputDtoNamespace,
                        $outputDtoClassName
                    );

                    $filesToGenerate[]   = $serviceInterface;
                    $serviceInterfaces[] = $serviceInterface;
                }
            }
        }

        $filesToGenerate[] = $this->serviceSubscriberFactory->generateServiceSubscriber(
            $this->namingStrategy->buildPath($this->rootPath, self::SERVICE_SUBSCRIBER_NAMESPACE),
            self::SERVICE_SUBSCRIBER_CLASSNAME . '.php',
            $this->namingStrategy->buildNamespace($this->rootNamespace, self::SERVICE_SUBSCRIBER_NAMESPACE),
            self::SERVICE_SUBSCRIBER_CLASSNAME,
            $serviceInterfaces
        );

        foreach ($filesToGenerate as $fileToGenerate) {
            $this->fileWriter->write(
                $fileToGenerate->getFileDirectoryPath(),
                $fileToGenerate->getFileName(),
                $fileToGenerate->getFileContents()
            );
        }

        return 0;
    }

    /**
     * @param Parameter[] $parameters
     * @param string $in
     * @return Parameter[]
     */
    private function filterAllowedParameters(array $parameters, string $in) : array
    {
        return array_filter($parameters, fn (Parameter $parameter) : bool => $parameter->in === $in);
    }

    private function generateDtoGraph(
        Schema $schema,
        string $dtoPath,
        string $dtoFileName,
        string $dtoNamespace,
        string $dtoClassName,
        bool $immutable
    ) : array {
        if ($schema->type !== Type::OBJECT) {
            return [];
        }

        /** @var GeneratedClass[] $filesToGenerate */
        return $this->dtoFactory->generateDtoClassGraph(
            $dtoPath,
            $dtoFileName,
            $dtoNamespace,
            $dtoClassName,
            $immutable,
            $schema
        );
    }

    private function findSpecifications() {
        $specs = [];
        foreach ($this->router->getRouteCollection()->all() as $route) {
            if($path = $route->getOption(RouteLoader::OPENAPI_SPEC_PATH)) {
                if(!isset($specs[$path])) {
                    $specs[$path] = $route->getOption(RouteLoader::OPENAPI_SPEC);
                }
            }
        }

        return $specs;
    }
}
