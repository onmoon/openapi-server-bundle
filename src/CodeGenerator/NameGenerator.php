<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use Lukasoppermann\Httpstatus\Httpstatus;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use Throwable;
use function ucfirst;

class NameGenerator
{
    private const DTO_NAMESPACE    = 'Dto';
    private const REQUEST_SUFFIX   = 'Request';
    private const RESPONSE_SUFFIX  = 'Response';
    public const DTO_SUFFIX        = 'Dto';
    public const APIS_NAMESPACE    = 'Apis';
    private const DUPLICATE_PREFIX = 'Property';

    private NamingStrategy $naming;
    private Httpstatus $httpstatus;
    private string $rootNamespace;
    private string $rootPath;

    public function __construct(NamingStrategy $naming, Httpstatus $httpstatus, string $rootNamespace, string $rootPath)
    {
        $this->naming        = $naming;
        $this->httpstatus    = $httpstatus;
        $this->rootNamespace = $rootNamespace;
        $this->rootPath      = $rootPath;
    }

    public function setAllNamesAndPaths(GraphDefinition $graph) : void
    {
        $graph->getServiceSubscriber()
            ->setFileName('ApiServiceLoaderServiceSubscriber.php')
            ->setFilePath($this->naming->buildPath($this->rootPath, 'ServiceSubscriber'))
            ->setClassName('ApiServiceLoaderServiceSubscriber')
            ->setNamespace($this->naming->buildNamespace($this->rootNamespace, 'ServiceSubscriber'));

        foreach ($graph->getSpecifications() as $specificationDefinition) {
            $specification = $specificationDefinition->getSpecification();
            $apiName       = $this->naming->stringToNamespace($specification->getNameSpace());
            $apiNamespace  = $this->naming->buildNamespace($this->rootNamespace, self::APIS_NAMESPACE, $apiName);
            $apiPath       = $this->naming->buildPath($this->rootPath, self::APIS_NAMESPACE, $apiName);

            foreach ($specificationDefinition->getOperations() as $operation) {
                $operationName      = $this->naming->stringToNamespace($operation->getOperationId());
                $operationNamespace = $this->naming->buildNamespace($apiNamespace, $operationName);
                $operationPath      = $this->naming->buildPath($apiPath, $operationName);

                $methodName = $this->naming->stringToMethodName($operation->getOperationId());
                $operation->getRequestHandlerInterface()
                    ->setMethodName($methodName)
                    ->setMethodDescription($operation->getSummary())
                    ->setFileName($this->getFileName($operationName))
                    ->setFilePath($operationPath)
                    ->setNamespace($operationNamespace)
                    ->setClassName($operationName);

                $request = $operation->getRequest();
                if ($request !== null) {
                    $this->setTreePropertyClassNames($request);
                    $this->setRequestNames($request, $operationNamespace, $operationName, $operationPath);
                    $this->setTreeGettersSetters($request);
                }

                $responseNamespace = $this->naming->buildNamespace(
                    $operationNamespace,
                    self::DTO_NAMESPACE,
                    self::RESPONSE_SUFFIX
                );

                $responsePath = $this->naming->buildPath(
                    $operationPath,
                    self::DTO_NAMESPACE,
                    self::RESPONSE_SUFFIX
                );

                foreach ($operation->getResponses() as $response) {
                    $this->setTreePropertyClassNames($response);
                    $this->setResponseNames($response, $responseNamespace, $operationName, $responsePath);
                    $this->setTreeGettersSetters($response);
                }

                $markersInterface = $operation->getMarkersInterface();
                if (! ($markersInterface instanceof GeneratedInterfaceDefinition)) {
                    continue;
                }

                $interfaceName = $this->naming->stringToNamespace($operationName . self::RESPONSE_SUFFIX);
                $markersInterface
                    ->setFileName($this->getFileName($interfaceName))
                    ->setFilePath($responsePath)
                    ->setClassName($interfaceName)
                    ->setNamespace($responseNamespace);
            }
        }
    }

    public function setRequestNames(RequestDtoDefinition $request, string $operationNamespace, string $operationName, string $operationPath) : void
    {
        $requestDtoNamespace = $this->naming->buildNamespace(
            $operationNamespace,
            self::DTO_NAMESPACE,
            self::REQUEST_SUFFIX
        );
        $requestDtoClassName = $this->naming->stringToNamespace(
            $operationName . self::REQUEST_SUFFIX . self::DTO_SUFFIX
        );
        $requestDtoPath      = $this->naming->buildPath(
            $operationPath,
            self::DTO_NAMESPACE,
            self::REQUEST_SUFFIX
        );

        $this->setTreePathsAndClassNames($request, $requestDtoNamespace, $requestDtoClassName, $requestDtoPath);
    }

    public function setResponseNames(ResponseDtoDefinition $response, string $responseNamespace, string $operationName, string $responsePath) : void
    {
        try {
            $statusNamespace = $this->httpstatus->getReasonPhrase((string) $response->getStatusCode());
        } catch (Throwable $e) {
            $statusNamespace = (string) $response->getStatusCode();
        }

        $statusNamespace = $this->naming->stringToNamespace($statusNamespace);

        $responseDtoNamespace = $this->naming->buildNamespace($responseNamespace, $statusNamespace);
        $responseDtoClassName = $this->naming->stringToNamespace(
            $operationName . $statusNamespace . self::DTO_SUFFIX
        );
        $responseDtoPath      = $this->naming->buildPath($responsePath, $statusNamespace);

        $this->setTreePathsAndClassNames($response, $responseDtoNamespace, $responseDtoClassName, $responseDtoPath);
    }

    public function setTreePathsAndClassNames(DtoDefinition $root, string $namespace, string $className, string $path) : void
    {
        $root->setClassName($className);
        $root->setFileName($this->getFileName($className));
        $root->setFilePath($path);
        $root->setNamespace($namespace);

        foreach ($root->getProperties() as $property) {
            $objectDefinition = $property->getObjectTypeDefinition();
            if ($objectDefinition === null) {
                continue;
            }

            $part         = $this->naming->stringToNamespace($property->getClassPropertyName());
            $subClassName = $this->naming->stringToNamespace($part . self::DTO_SUFFIX);

            if ($subClassName === $className) {
                //ToDo: check if more elegant way exists
                $subClassName = self::DUPLICATE_PREFIX . $subClassName;
            }

            $subNamespace = $this->naming->buildNamespace($namespace, $part);
            $subPath      = $this->naming->buildPath($path, $part);
            $this->setTreePathsAndClassNames($objectDefinition, $subNamespace, $subClassName, $subPath);
        }
    }

    public function getFileName(string $className) : string
    {
        return $className . '.php';
    }

    public function setTreeGettersSetters(DtoDefinition $root) : void
    {
        foreach ($root->getProperties() as $property) {
            $baseName = ucfirst($this->naming->stringToMethodName($property->getClassPropertyName()));
            $property->setGetterName('get' . $baseName);
            $property->setSetterName('set' . $baseName);

            $objectDefinition = $property->getObjectTypeDefinition();
            if ($objectDefinition === null) {
                continue;
            }

            $this->setTreeGettersSetters($objectDefinition);
        }
    }

    public function setTreePropertyClassNames(DtoDefinition $root) : void
    {
        foreach ($root->getProperties() as $property) {
            $propertyName = $property->getSpecPropertyName();

            if (! $this->naming->isAllowedPhpPropertyName($propertyName)) {
                $propertyName = $this->naming->stringToMethodName($propertyName);
            }

            $property->setClassPropertyName($propertyName);

            $objectDefinition = $property->getObjectTypeDefinition();
            if ($objectDefinition === null) {
                continue;
            }

            $this->setTreePropertyClassNames($objectDefinition);
        }
    }
}
