<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator;


use Lukasoppermann\Httpstatus\Httpstatus;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;

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

                foreach ($operation->getResponses() as $response) {
                    $this->setResponseNames($response, $operationNamespace, $operationName, $operationPath);
                }
            }
        }
    }

    private function setResponseNames(ResponseDtoDefinition $response, string $operationNamespace, string $operationName, string $operationPath) {
        try {
            $statusNamespace = $this->httpstatus->getReasonPhrase($response->getStatusCode());
        } catch (\Throwable $e) {
            $statusNamespace = $response->getStatusCode();
        }

        $statusNamespace = $this->naming->stringToNamespace($statusNamespace);

        $responseDtoNamespace = $this->naming->buildNamespace(
            $operationNamespace,
            self::DTO_NAMESPACE,
            self::RESPONSE_SUFFIX,
            $statusNamespace
        );
        $responseDtoClassName = $this->naming->stringToNamespace(
            $operationName . $statusNamespace . self::DTO_SUFFIX
        );
        $responseDtoPath      = $this->naming->buildPath(
            $operationPath,
            self::DTO_NAMESPACE,
            self::RESPONSE_SUFFIX,
            $statusNamespace
        );

        $this->setTreeNames($response, $responseDtoClassName, $responseDtoNamespace, $responseDtoPath);
    }

    private function setTreeNames(DtoDefinition $root, string $className, string $namespace, string $path) {
        $fileName  = $className . '.php';

        $root->setClassName($className);
        $root->setFileName($this->naming->buildPath($path, $fileName));
        $root->setNamespace($namespace);

        foreach ($root->getProperties() as $property) {
            $objectDefinition = $property->getObjectTypeDefinition();
            if($objectDefinition !== null) {
                $part = $this->naming->stringToNamespace($property->getClassPropertyName());
                $subClassName = $this->naming->stringToNamespace($part . self::DTO_SUFFIX);
                $subNamespace = $this->naming->buildNamespace($namespace, $part);
                $subPath = $this->naming->buildPath($path, $part);
                $this->setTreeNames($objectDefinition, $subClassName, $subNamespace, $subPath);
            }
        }
    }
}