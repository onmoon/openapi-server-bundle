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
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\Factory\RequestBodyDtoDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\Factory\RequestDtoDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\Factory\ResponseDtoDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\Factory\ResponseDtoMarkerInterfaceDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SchemaBasedDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\DtoFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\RequestDtoFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FileWriter;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\Factory\RequestHandlerInterfaceDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\RequestHandlerInterfaceFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\Definitions\Factory\ServiceSubscriberDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\ServiceSubscriberFactory;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\RequestBodyDtoGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\RequestDtoGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\RequestHandlerInterfaceGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ResponseDtoGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ResponseDtoMarkerInterfaceGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ServiceSubscriberGenerationEvent;
use OnMoon\OpenApiServerBundle\Exception\CannotGenerateCodeForOperation;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use function array_filter;
use function array_key_exists;
use function array_merge;
use function count;

class ApiServerCodeGenerator
{
    private EventDispatcherInterface $eventDispatcher;
    private NamingStrategy $namingStrategy;
    private RequestDtoFactory $requestDtoFactory;
    private DtoFactory $dtoFactory;
    private RequestHandlerInterfaceFactory $requestHandlerInterfaceFactory;
    private ServiceSubscriberFactory $serviceSubscriberFactory;
    private FileWriter $fileWriter;
    private SpecificationLoader $loader;
    private Httpstatus $httpstatus;
    private string $rootNamespace;
    private string $rootPath;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        NamingStrategy $namingStrategy,
        RequestDtoFactory $requestDtoFactory,
        DtoFactory $dtoFactory,
        RequestHandlerInterfaceFactory $requestHandlerInterfaceFactory,
        ServiceSubscriberFactory $serviceSubscriberFactory,
        FileWriter $fileWriter,
        SpecificationLoader $loader,
        Httpstatus $httpstatus,
        string $rootNamespace,
        string $rootPath
    ) {
        $this->eventDispatcher                = $eventDispatcher;
        $this->namingStrategy                 = $namingStrategy;
        $this->requestDtoFactory              = $requestDtoFactory;
        $this->dtoFactory                     = $dtoFactory;
        $this->requestHandlerInterfaceFactory = $requestHandlerInterfaceFactory;
        $this->serviceSubscriberFactory       = $serviceSubscriberFactory;
        $this->fileWriter                     = $fileWriter;
        $this->loader                         = $loader;
        $this->httpstatus                     = $httpstatus;
        $this->rootNamespace                  = $rootNamespace;
        $this->rootPath                       = $rootPath;
    }

    public function generate() : void
    {
        /** @var GeneratedClass[] $filesToGenerate */
        $filesToGenerate = [];
        /** @var RequestHandlerInterfaceDefinition[] $requestHandlerInterfaces */
        $requestHandlerInterfaces = [];

        foreach ($this->loader->list() as $specificationName => $specification) {
            $parsedSpecification = $this->loader->load($specificationName);

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

                    if ($operationId === '') {
                        throw CannotGenerateCodeForOperation::becauseNoOperationIdSpecified(
                            $url,
                            $method,
                            $specification->getPath()
                        );
                    }

                    $requestDtoDefinition                 = null;
                    $requestBodyDtoDefinition             = null;
                    $responseDtoDefinitions               = [];
                    $responseDtoMarkerInterfaceDefinition = null;

                    $requestBody = $operation->requestBody;
                    $responses   = $operation->responses;

                    // Request body dto generation

                    if ($requestBody instanceof RequestBody &&
                        array_key_exists($specification->getMediaType(), $requestBody->content)
                    ) {
                        $mediaType = $requestBody->content[$specification->getMediaType()];

                        if ($mediaType->schema instanceof Schema) {
                            $requestBodyDtoDefinition = (new RequestBodyDtoDefinitionFactory(
                                $specification,
                                $operation,
                                $this->namingStrategy,
                                $this->rootNamespace,
                                $this->rootPath
                            ))->create($mediaType->schema);

                            $this->eventDispatcher->dispatch(
                                new RequestBodyDtoGenerationEvent($requestBodyDtoDefinition)
                            );

                            /** @var GeneratedClass[] $filesToGenerate */
                            $filesToGenerate = array_merge(
                                $filesToGenerate,
                                $this->generateDtoGraph(
                                    $url,
                                    $method,
                                    'request',
                                    $specification->getPath(),
                                    $requestBodyDtoDefinition
                                )
                            );
                        }
                    }

                    // Response dtos generation

                    if ($responses instanceof Responses) {
                        /**
                         * @var string $responseCode
                         */
                        foreach ($responses->getResponses() as $responseCode => $response) {
                            if (! ($response instanceof Response)) {
                                continue;
                            }

                            if (! array_key_exists($specification->getMediaType(), $response->content) ||
                                ! $response->content[$specification->getMediaType()] instanceof MediaType
                            ) {
                                continue;
                            }

                            /** @var MediaType $mediaType */
                            $mediaType = $response->content[$specification->getMediaType()];

                            if (! ($mediaType->schema instanceof Schema)) {
                                continue;
                            }

                            $responseDtoDefinition = (new ResponseDtoDefinitionFactory(
                                $specification,
                                $operation,
                                $this->namingStrategy,
                                $this->httpstatus,
                                $this->rootNamespace,
                                $this->rootPath
                            ))->create($mediaType->schema, (string) $responseCode);

                            $this->eventDispatcher->dispatch(new ResponseDtoGenerationEvent($responseDtoDefinition));

                            $responseDtoDefinitions[] = $responseDtoDefinition;
                        }

                        if (count($responseDtoDefinitions) > 1) {
                            $responseDtoMarkerInterfaceDefinition = (new ResponseDtoMarkerInterfaceDefinitionFactory(
                                $specification,
                                $operation,
                                $this->namingStrategy,
                                $this->rootNamespace,
                                $this->rootPath
                            ))->create();

                            $this->eventDispatcher->dispatch(
                                new ResponseDtoMarkerInterfaceGenerationEvent($responseDtoMarkerInterfaceDefinition)
                            );

                            $filesToGenerate[] = $this->dtoFactory->generateResponseMarkerInterface(
                                $responseDtoMarkerInterfaceDefinition
                            );

                            foreach ($responseDtoDefinitions as $responseDtoDefinition) {
                                $responseDtoDefinition->setMarkerInterfaceDefinition(
                                    $responseDtoMarkerInterfaceDefinition
                                );
                            }
                        }

                        foreach ($responseDtoDefinitions as $responseDtoDefinition) {
                            /** @var GeneratedClass[] $filesToGenerate */
                            $filesToGenerate = array_merge(
                                $filesToGenerate,
                                $this->generateDtoGraph(
                                    $url,
                                    $method,
                                    'response (code "' . $responseDtoDefinition->responseCode() . '")',
                                    $specification->getPath(),
                                    $responseDtoDefinition
                                )
                            );
                        }
                    }

                    // Request dto generation

                    $parameters = $this->mergeParameters($pathItem, $operation);

                    if (count($parameters) || $requestBodyDtoDefinition !== null) {
                        $requestDtoDefinition = (new RequestDtoDefinitionFactory(
                            $specification,
                            $operation,
                            $this->namingStrategy,
                            $this->rootNamespace,
                            $this->rootPath
                        ))->create($requestBodyDtoDefinition, ...$parameters);

                        $this->eventDispatcher->dispatch(new RequestDtoGenerationEvent($requestDtoDefinition));

                        $filesToGenerate = array_merge(
                            $filesToGenerate,
                            $this->requestDtoFactory->generateDto($requestDtoDefinition)
                        );
                    }

                    // Request handler interface generation

                    $requestHandlerInterfaceDefinition = (new RequestHandlerInterfaceDefinitionFactory(
                        $specification,
                        $operation,
                        $this->namingStrategy,
                        $this->rootNamespace,
                        $this->rootPath
                    ))->create(
                        $requestDtoDefinition,
                        $summary,
                        $responseDtoMarkerInterfaceDefinition,
                        ...$responseDtoDefinitions
                    );

                    $this->eventDispatcher->dispatch(
                        new RequestHandlerInterfaceGenerationEvent($requestHandlerInterfaceDefinition)
                    );

                    $requestHandlerInterface = $this->requestHandlerInterfaceFactory->generateInterface(
                        $requestHandlerInterfaceDefinition
                    );

                    $filesToGenerate[]          = $requestHandlerInterface;
                    $requestHandlerInterfaces[] = $requestHandlerInterfaceDefinition;
                }
            }
        }

        // RequestHandler subscriber generation

        $serviceSubscriberDefinition = (new ServiceSubscriberDefinitionFactory(
            $this->namingStrategy,
            $this->rootNamespace,
            $this->rootPath
        ))->create(
            ...$requestHandlerInterfaces
        );

        $this->eventDispatcher->dispatch(new ServiceSubscriberGenerationEvent($serviceSubscriberDefinition));

        $filesToGenerate[] = $this->serviceSubscriberFactory->generateServiceSubscriber(
            $serviceSubscriberDefinition
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
        string $url,
        string $method,
        string $location,
        string $specificationFilePath,
        SchemaBasedDtoDefinition $definition
    ) : array {
        if ($definition->schema()->type !== Type::OBJECT) {
            $isArray = ($definition->schema()->type === Type::ARRAY);

            throw CannotGenerateCodeForOperation::becauseRootIsNotObject(
                $url,
                $method,
                $location,
                $specificationFilePath,
                $isArray
            );
        }

        return $this->dtoFactory->generateDtoClassGraph($definition);
    }
}
