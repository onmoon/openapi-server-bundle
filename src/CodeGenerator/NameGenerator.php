<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use Lukasoppermann\Httpstatus\Httpstatus;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use Throwable;

use function ucfirst;

final class NameGenerator
{
    private const DTO_NAMESPACE       = 'Dto';
    private const REQUEST_SUFFIX      = 'Request';
    private const RESPONSE_SUFFIX     = 'Response';
    public const DTO_SUFFIX           = 'Dto';
    public const APIS_NAMESPACE       = 'Apis';
    public const COMPONENTS_NAMESPACE = 'Components';

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

    public function setAllNamesAndPaths(GraphDefinition $graph): void
    {
        $graph->getServiceSubscriber()
            ->setFileName('ApiServiceLoaderServiceSubscriber.php')
            ->setFilePath($this->naming->buildPath($this->rootPath, 'ServiceSubscriber'))
            ->setClassName('ApiServiceLoaderServiceSubscriber')
            ->setNamespace($this->naming->buildNamespace($this->rootNamespace, 'ServiceSubscriber'));

        foreach ($graph->getSpecifications() as $specificationDefinition) {
            $specification       = $specificationDefinition->getSpecification();
            $apiName             = $this->naming->stringToNamespace($specification->getNameSpace());
            $apiNamespace        = $this->naming->buildNamespace($this->rootNamespace, self::APIS_NAMESPACE, $apiName);
            $apiPath             = $this->naming->buildPath($this->rootPath, self::APIS_NAMESPACE, $apiName);
            $componentsNamespace = $this->naming->buildNamespace($this->rootNamespace, self::COMPONENTS_NAMESPACE, $apiName);
            $componentsPath      = $this->naming->buildPath($this->rootPath, self::COMPONENTS_NAMESPACE, $apiName);

            foreach ($specificationDefinition->getComponents() as $component) {
                $componentName      = $this->naming->stringToNamespace($component->getName());
                $componentNamespace = $this->naming->buildNamespace($componentsNamespace, $componentName);
                $componentPath      = $this->naming->buildPath($componentsPath, $componentName);

                $this->setTreeNames($component->getDto(), $componentNamespace, $componentName, $componentPath);
            }

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
                if ($request instanceof DtoDefinition) {
                    $this->setRequestNames($request, $operationNamespace, $operationName, $operationPath);
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
                    $this->setResponseNames($response, $responseNamespace, $operationName, $responsePath);
                }
            }
        }
    }

    public function setRequestNames(DtoDefinition $request, string $operationNamespace, string $operationName, string $operationPath): void
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

        $this->setTreeNames($request, $requestDtoNamespace, $requestDtoClassName, $requestDtoPath);
    }

    public function setResponseNames(ResponseDefinition $response, string $responseNamespace, string $operationName, string $responsePath): void
    {
        $responseBody = $response->getResponseBody();
        if (! $responseBody instanceof DtoDefinition) {
            return;
        }

        try {
            $statusNamespace = $this->httpstatus->getReasonPhrase((int) $response->getStatusCode());
        } catch (Throwable $e) {
            $statusNamespace = $response->getStatusCode();
        }

        $statusNamespace = $this->naming->stringToNamespace($statusNamespace);

        $responseDtoNamespace = $this->naming->buildNamespace($responseNamespace, $statusNamespace);
        $responseDtoClassName = $this->naming->stringToNamespace(
            $operationName . $statusNamespace . self::DTO_SUFFIX
        );
        $responseDtoPath      = $this->naming->buildPath($responsePath, $statusNamespace);

        $this->setTreeNames($responseBody, $responseDtoNamespace, $responseDtoClassName, $responseDtoPath);
    }

    public function setTreeNames(DtoDefinition $root, string $namespace, string $className, string $path): void
    {
        $root->setClassName($className);
        $root->setFileName($this->getFileName($className));
        $root->setFilePath($path);
        $root->setNamespace($namespace);
        $this->setPropertyClassNames($root);
        $this->setGettersSetters($root);

        foreach ($root->getProperties() as $property) {
            $objectDefinition = $property->getObjectTypeDefinition();
            if (! $objectDefinition instanceof DtoDefinition) {
                continue;
            }

            $part         = $this->naming->stringToNamespace($property->getClassPropertyName());
            $subClassName = $this->naming->stringToNamespace($part . self::DTO_SUFFIX);
            $subNamespace = $this->naming->buildNamespace($namespace, $part);
            $subPath      = $this->naming->buildPath($path, $part);
            $this->setTreeNames($objectDefinition, $subNamespace, $subClassName, $subPath);
        }
    }

    public function getFileName(string $className): string
    {
        return $className . '.php';
    }

    public function setGettersSetters(DtoDefinition $root): void
    {
        foreach ($root->getProperties() as $property) {
            $baseName = ucfirst($this->naming->stringToMethodName($property->getClassPropertyName()));
            $property->setGetterName('get' . $baseName);
            $property->setSetterName('set' . $baseName);
        }
    }

    public function setPropertyClassNames(DtoDefinition $root): void
    {
        foreach ($root->getProperties() as $property) {
            $propertyName = $property->getSpecPropertyName();

            if (! $this->naming->isAllowedPhpPropertyName($propertyName)) {
                $propertyName = $this->naming->stringToMethodName($propertyName);
            }

            $property->setClassPropertyName($propertyName);
        }
    }
}
