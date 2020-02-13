<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Lukasoppermann\Httpstatus\Httpstatus;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\DtoFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\RootDtoFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FileWriter;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceInterface\ServiceInterfaceFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\ServiceSubscriberFactory;
use OnMoon\OpenApiServerBundle\Exception\CannotGenerateCodeForOperation;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use Throwable;
use function array_filter;
use function array_key_exists;
use function array_merge;
use function count;

class ApiServerCodeGenerator
{
    public const APIS_NAMESPACE                = 'Apis';
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
    private SpecificationLoader $loader;
    private Httpstatus $httpstatus;
    private string $rootNamespace;
    private string $rootPath;

    public function __construct(
        NamingStrategy $namingStrategy,
        RootDtoFactory $rootDtoFactory,
        DtoFactory $dtoFactory,
        ServiceInterfaceFactory $serviceInterfaceFactory,
        ServiceSubscriberFactory $serviceSubscriberFactory,
        FileWriter $fileWriter,
        SpecificationLoader $loader,
        Httpstatus $httpstatus,
        string $rootNamespace,
        string $rootPath
    ) {
        $this->namingStrategy           = $namingStrategy;
        $this->rootDtoFactory           = $rootDtoFactory;
        $this->dtoFactory               = $dtoFactory;
        $this->serviceInterfaceFactory  = $serviceInterfaceFactory;
        $this->serviceSubscriberFactory = $serviceSubscriberFactory;
        $this->fileWriter               = $fileWriter;
        $this->loader                   = $loader;
        $this->httpstatus               = $httpstatus;
        $this->rootNamespace            = $rootNamespace;
        $this->rootPath                 = $rootPath;
    }

    public function generate() : void
    {
        /** @var GeneratedClass[] $filesToGenerate */
        $filesToGenerate = [];
        /** @var GeneratedClass[] $serviceInterfaces */
        $serviceInterfaces = [];

        foreach ($this->loader->list() as $specificationName => $specification) {
            $parsedSpecification = $this->loader->load($specificationName);

            $apiName       = $specification->getNameSpace();
            $specMediaType = $specification->getMediaType();
            $apiNamespace  = $this->namingStrategy->buildNamespace($this->rootNamespace, self::APIS_NAMESPACE, $apiName);
            $apiPath       = $this->namingStrategy->buildPath($this->rootPath, self::APIS_NAMESPACE, $apiName);

            /**
             * @var string $url
             */
            foreach ($parsedSpecification->paths as $url => $pathItem) {
                /**
                 * @var string $method
                 */
                foreach ($pathItem->getOperations() as $method => $operation) {
                    $operationId = $operation->operationId;
                    $summary     = $operation->summary;

                    $operationName      = $this->namingStrategy->stringToNamespace($operationId);
                    $operationNamespace = $this->namingStrategy->buildNamespace($apiNamespace, $operationName);
                    $operationPath      = $this->namingStrategy->buildPath($apiPath, $operationName);

                    if ($operationId === '') {
                        throw CannotGenerateCodeForOperation::becauseNoOperationIdSpecified(
                            $url,
                            $method,
                            $specification->getPath()
                        );
                    }

                    $rootDtoNamespace  = null;
                    $rootDtoClassName  = null;
                    $inputDtoNamespace = null;
                    $inputDtoClassName = null;
                    /** @psalm-var list<array{namespace: string, className: string, code: int}> $outputDtos */
                    $outputDtos                        = [];
                    $outputDtoMarkerInterfaceNamespace = null;
                    $outputDtoMarkerInterfaceClassName = null;

                    $requestBody = $operation->requestBody;
                    $responses   = $operation->responses;

                    if ($requestBody instanceof RequestBody &&
                        array_key_exists($specMediaType, $requestBody->content)
                    ) {
                        $mediaType = $requestBody->content[$specMediaType];

                        if ($mediaType->schema instanceof Schema) {
                            $schema = $mediaType->schema;

                            $dtoNamespace = $this->namingStrategy->buildNamespace(
                                $operationNamespace,
                                self::DTO_NAMESPACE,
                                self::REQUEST_SUFFIX
                            );
                            $dtoClassName = $this->namingStrategy->stringToNamespace(
                                $operationName . self::REQUEST_BODY_SUFFIX . self::DTO_SUFFIX
                            );
                            $dtoPath      = $this->namingStrategy->buildPath(
                                $operationPath,
                                self::DTO_NAMESPACE,
                                self::REQUEST_SUFFIX
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

                    if ($responses instanceof Responses) {
                        $outputDtosToGenerate = [];
                        /**
                         * @var int $responseCode
                         */
                        foreach ($responses->getResponses() as $responseCode => $response) {
                            if (! ($response instanceof Response)) {
                                continue;
                            }

                            if (! array_key_exists($specMediaType, $response->content) ||
                                ! $response->content[$specMediaType] instanceof MediaType
                            ) {
                                continue;
                            }

                            $mediaType = $response->content[$specMediaType];

                            if (! ($mediaType->schema instanceof Schema)) {
                                continue;
                            }

                            $schema = $mediaType->schema;

                            try {
                                $statusNamespace = $this->httpstatus->getReasonPhrase((string) $responseCode);
                            } catch (Throwable $e) {
                                $statusNamespace = (string) $responseCode;
                            }

                            $statusNamespace = $this->namingStrategy->stringToNamespace($statusNamespace);

                            $dtoNamespace = $this->namingStrategy->buildNamespace(
                                $operationNamespace,
                                self::DTO_NAMESPACE,
                                self::RESPONSE_SUFFIX,
                                $statusNamespace
                            );
                            $dtoClassName = $this->namingStrategy->stringToNamespace(
                                $operationName . self::RESPONSE_SUFFIX . self::DTO_SUFFIX
                            );
                            $dtoPath      = $this->namingStrategy->buildPath(
                                $operationPath,
                                self::DTO_NAMESPACE,
                                self::RESPONSE_SUFFIX,
                                $statusNamespace
                            );
                            $dtoFileName  = $dtoClassName . '.php';

                            $outputDtos[]           = [
                                'namespace' => $dtoNamespace,
                                'className' => $dtoClassName,
                                'code' => $responseCode,
                            ];
                            $outputDtosToGenerate[] = [
                                'schema' => $schema,
                                'dtoPath' => $dtoPath,
                                'dtoFileName' => $dtoFileName,
                                'dtoNameSpace' => $dtoNamespace,
                                'dtoClassName' => $dtoClassName,
                                'responseCode' => (int) $responseCode === 0 ? 200 : (int) $responseCode,
                            ];
                        }

                        if (count($outputDtos) > 1) {
                            $outputDtoMarkerInterfaceNamespace = $this->namingStrategy->buildNamespace(
                                $operationNamespace,
                                self::DTO_NAMESPACE,
                                self::RESPONSE_SUFFIX
                            );
                            $outputDtoMarkerInterfaceClassName = $this->namingStrategy->stringToNamespace(
                                $operationName . self::RESPONSE_SUFFIX
                            );
                            $interfacePath                     = $this->namingStrategy->buildPath(
                                $operationPath,
                                self::DTO_NAMESPACE,
                                self::RESPONSE_SUFFIX
                            );
                            $interfaceFileName                 = $outputDtoMarkerInterfaceClassName . '.php';

                            $filesToGenerate[] = $this->dtoFactory->generateOutputMarkerInterface(
                                $interfacePath,
                                $interfaceFileName,
                                $outputDtoMarkerInterfaceNamespace,
                                $outputDtoMarkerInterfaceClassName
                            );
                        }

                        foreach ($outputDtosToGenerate as $outputDtoToGenerate) {
                            /** @var GeneratedClass[] $filesToGenerate */
                            $filesToGenerate = array_merge(
                                $filesToGenerate,
                                $this->generateDtoGraph(
                                    $outputDtoToGenerate['schema'],
                                    $outputDtoToGenerate['dtoPath'],
                                    $outputDtoToGenerate['dtoFileName'],
                                    $outputDtoToGenerate['dtoNameSpace'],
                                    $outputDtoToGenerate['dtoClassName'],
                                    false,
                                    $outputDtoToGenerate['responseCode'],
                                    $outputDtoMarkerInterfaceNamespace,
                                    $outputDtoMarkerInterfaceClassName
                                )
                            );
                        }
                    }

                    // Root dto generation

                    $parameters = $this->mergeParameters($pathItem, $operation);

                    if (count($parameters) || $inputDtoClassName !== null) {
                        $dtoNamespace = $this->namingStrategy->buildNamespace(
                            $operationNamespace,
                            self::DTO_NAMESPACE,
                            self::REQUEST_SUFFIX
                        );
                        $dtoClassName = $this->namingStrategy->stringToNamespace(
                            $operationName . self::REQUEST_SUFFIX . self::DTO_SUFFIX
                        );
                        $dtoPath      = $this->namingStrategy->buildPath(
                            $operationPath,
                            self::DTO_NAMESPACE,
                            self::REQUEST_SUFFIX
                        );
                        $dtoFileName  = $dtoClassName . '.php';

                        $rootDtoNamespace = $dtoNamespace;
                        $rootDtoClassName = $dtoClassName;

                        $filesToGenerate = array_merge(
                            $filesToGenerate,
                            $this->rootDtoFactory->generateRootDto(
                                $dtoPath,
                                $dtoFileName,
                                $dtoNamespace,
                                $dtoClassName,
                                $inputDtoNamespace,
                                $inputDtoClassName,
                                $this->filterSupportedParameters($parameters, 'path'),
                                $this->filterSupportedParameters($parameters, 'query')
                            )
                        );
                    }

                    // Service interface generation

                    $operationName             = $this->namingStrategy->stringToNamespace($operationId);
                    $serviceInterfaceNamesapce = $this->namingStrategy->buildNamespace($apiNamespace, $operationName);
                    $serviceInterfacePath      = $this->namingStrategy->buildPath($apiPath, $operationName);

                    $serviceInterfaceClassName = $this->namingStrategy->stringToNamespace($operationName);
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
                        $outputDtos,
                        $outputDtoMarkerInterfaceNamespace,
                        $outputDtoMarkerInterfaceClassName
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
    }

    /**
     * @param Parameter[] $parameters
     *
     * @return Parameter[]
     */
    private function filterSupportedParameters(array $parameters, string $in) : array
    {
        return array_filter($parameters, static fn ($parameter) : bool => $parameter->in === $in);
    }

    /**
     * @param Parameter[]|Reference[] $parameters
     *
     * @return Parameter[]
     */
    private function filterParameters(array $parameters) : array
    {
        /** @var Parameter[] $parameters */
        $parameters = array_filter($parameters, static fn ($parameter) : bool => $parameter instanceof Parameter);

        return $parameters;
    }

    /**
     * @return Parameter[]
     */
    private function mergeParameters(PathItem $pathItem, Operation $operation) : array
    {
        $operationParameters = $this->filterParameters($operation->parameters);

        return array_merge(
            array_filter(
                $this->filterParameters($pathItem->parameters),
                static function (Parameter $pathParameter) use ($operationParameters) : bool {
                    return count(
                        array_filter(
                            $operationParameters,
                            static function (Parameter $operationParameter) use ($pathParameter) : bool {
                                return $operationParameter->name === $pathParameter->name &&
                                       $operationParameter->in === $pathParameter->in;
                            }
                        )
                    ) === 0;
                }
            ),
            $operationParameters
        );
    }

    /**
     * @return GeneratedClass[]
     */
    private function generateDtoGraph(
        Schema $schema,
        string $dtoPath,
        string $dtoFileName,
        string $dtoNamespace,
        string $dtoClassName,
        bool $immutable,
        ?int $outputResponseCode = null,
        ?string $outputMarkerInterfaceNamespace = null,
        ?string $outputMarkerInterfaceClassName = null
    ) : array {
        if ($schema->type !== Type::OBJECT) {
            return [];
        }

        return $this->dtoFactory->generateDtoClassGraph(
            $dtoPath,
            $dtoFileName,
            $dtoNamespace,
            $dtoClassName,
            $immutable,
            $schema,
            $outputResponseCode,
            $outputMarkerInterfaceNamespace,
            $outputMarkerInterfaceClassName
        );
    }
}
