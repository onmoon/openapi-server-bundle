<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use Lukasoppermann\Httpstatus\Httpstatus;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\GeneratedInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use Throwable;

class NameGenerator
{
    private const DTO_NAMESPACE   = 'Dto';
    private const REQUEST_SUFFIX  = 'Request';
    private const RESPONSE_SUFFIX = 'Response';
    public const DTO_SUFFIX       = 'Dto';
    private const APIS_NAMESPACE  = 'Apis';

    private NamingStrategy $naming;
    private Httpstatus $httpstatus;
    private string $rootNamespace;
    private string $rootPath;

    /**
     * NameGenerator constructor.
     * @param NamingStrategy $naming
     * @param Httpstatus $httpstatus
     * @param string $rootNamespace
     * @param string $rootPath
     */
    public function __construct(NamingStrategy $naming, Httpstatus $httpstatus, string $rootNamespace, string $rootPath)
    {
        $this->naming = $naming;
        $this->httpstatus = $httpstatus;
        $this->rootNamespace = $rootNamespace;
        $this->rootPath = $rootPath;
    }

    /**
     * @param SpecificationDefinition[] $specificationDefinitions
     */
    public function generate(array $specificationDefinitions) {
        foreach ($specificationDefinitions as $specificationDefinition) {
            $specification = $specificationDefinition->getSpecification();
            $apiName       = $this->naming->stringToNamespace($specification->getNameSpace());
            $apiNamespace  = $this->naming->buildNamespace($this->rootNamespace, self::APIS_NAMESPACE, $apiName);
            $apiPath = $this->naming->buildPath($this->rootPath, self::APIS_NAMESPACE, $apiName);

            foreach ($specificationDefinition->getOperations() as $operation) {
                $operationName      = $this->naming->stringToNamespace($operation->getOperationId());
                $operationNamespace = $this->naming->buildNamespace($apiNamespace, $operationName);
                $operationPath      = $this->naming->buildPath($apiPath, $operationName);

                $methodName = $this->naming->stringToMethodName($operation->getOperationId());
                $operation->getServiceInterface()
                    ->setNamespace($operationNamespace)
                    ->setClassName($operationName)
                    ->setFileName($this->getFileName($operationName))
                    ->setFilePath($operationPath)
                    ->setMethodName($methodName)
                    ->setMethodDescription($operation->getSummary());

                if($operation->getRequest() !== null) {
                    $this->setRequestNames($operation->getRequest(), $operationNamespace, $operationName, $operationPath);
                    $this->setTreeGettersSetters($operation->getRequest());
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
                    $this->setTreeGettersSetters($response);
                }

                $markersInterface = $operation->getMarkersInterface();
                if($markersInterface instanceof GeneratedInterfaceDefinition) {
                   $interfaceName = $this->naming->stringToNamespace($operationName . self::RESPONSE_SUFFIX);
                   $markersInterface
                       ->setClassName($interfaceName)
                       ->setNamespace($responseNamespace)
                       ->setFileName($this->getFileName($interfaceName))
                       ->setFilePath($responsePath);
                }
            }
        }
    }

    private function setRequestNames(RequestDtoDefinition $request, string $operationNamespace, string $operationName, string $operationPath) {
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

    private function setResponseNames(ResponseDtoDefinition $response, string $responseNamespace, string $operationName, string $responsePath) {
        try {
            $statusNamespace = $this->httpstatus->getReasonPhrase($response->getStatusCode());
        } catch (Throwable $e) {
            $statusNamespace = $response->getStatusCode();
        }

        $statusNamespace = $this->naming->stringToNamespace($statusNamespace);

        $responseDtoNamespace = $this->naming->buildNamespace($responseNamespace, $statusNamespace);
        $responseDtoClassName = $this->naming->stringToNamespace(
            $operationName . $statusNamespace . self::DTO_SUFFIX
        );
        $responseDtoPath      = $this->naming->buildPath($responsePath, $statusNamespace);

        $this->setTreeNames($response, $responseDtoNamespace, $responseDtoClassName, $responseDtoPath);
    }
//ToDo: back to private
    public function setTreeNames(DtoDefinition $root, string $namespace, string $className, string $path) {
        $root->setClassName($className);
        $root->setFileName($this->getFileName($className));
        $root->setFilePath($path);
        $root->setNamespace($namespace);

        foreach ($root->getProperties() as $property) {
            $objectDefinition = $property->getObjectTypeDefinition();
            if($objectDefinition !== null) {
                $part = $this->naming->stringToNamespace($property->getClassPropertyName());
                $subClassName = $this->naming->stringToNamespace($part . self::DTO_SUFFIX);
                $subNamespace = $this->naming->buildNamespace($namespace, $part);
                $subPath = $this->naming->buildPath($path, $part);
                $this->setTreeNames($objectDefinition, $subNamespace, $subClassName, $subPath);
            }
        }
    }

    private function getFileName($className) {
        return $className . '.php';
    }

    private function setTreeGettersSetters(DtoDefinition $root) {
        foreach ($root->getProperties() as $property) {
            $baseName = ucfirst($this->naming->stringToMethodName($property->getClassPropertyName()));
            $property->setGetterName('get' . $baseName);
            $property->setSetterName('set' . $baseName);

            $objectDefinition = $property->getObjectTypeDefinition();
            if($objectDefinition !== null) {
                $this->setTreeGettersSetters($objectDefinition);
            }
        }
    }
}