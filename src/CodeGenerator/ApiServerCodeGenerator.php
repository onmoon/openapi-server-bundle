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
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\Factory\RequestBodyDtoDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\Factory\RequestDtoDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\Factory\ResponseDtoDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\Factory\ResponseDtoMarkerInterfaceDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SchemaBasedDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\DtoFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\RequestDtoFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FileWriter;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\Factory\RequestHandlerInterfaceDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\RequestHandlerInterfaceFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\Definitions\Factory\ServiceSubscriberDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber\ServiceSubscriberFactory;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ClassGraphReadyEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\FilesReadyEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\RequestBodyDtoGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\RequestDtoGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\RequestHandlerInterfaceGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ResponseDtoGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ResponseDtoMarkerInterfaceGenerationEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ServiceSubscriberGenerationEvent;
use OnMoon\OpenApiServerBundle\Exception\CannotGenerateCodeForOperation;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use function array_filter;
use function array_key_exists;
use function array_merge;
use function count;

class ApiServerCodeGenerator
{
    private GraphGenerator $graphGenerator;
    private NameGenerator $nameGenerator;
    private InterfaceGenerator $interfaceGenerator;
    private FileGenerator $filesGenerator;
    private AttributeGenerator $attributeGenerator;
    private FileWriter $writer;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * ApiServerCodeGenerator constructor.
     * @param GraphGenerator $graphGenerator
     * @param NameGenerator $nameGenerator
     * @param InterfaceGenerator $interfaceGenerator
     * @param FileGenerator $filesGenerator
     * @param AttributeGenerator $attributeGenerator
     * @param FileWriter $writer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(GraphGenerator $graphGenerator, NameGenerator $nameGenerator, InterfaceGenerator $interfaceGenerator, FileGenerator $filesGenerator, AttributeGenerator $attributeGenerator, FileWriter $writer, EventDispatcherInterface $eventDispatcher)
    {
        $this->graphGenerator = $graphGenerator;
        $this->nameGenerator = $nameGenerator;
        $this->interfaceGenerator = $interfaceGenerator;
        $this->filesGenerator = $filesGenerator;
        $this->attributeGenerator = $attributeGenerator;
        $this->writer = $writer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function generate() : void
    {
        $graph = $this->graphGenerator->generateClassGraph();
        $this->interfaceGenerator->setAllInterfaces($graph);
        $this->attributeGenerator->setAllAttributes($graph);
        $this->nameGenerator->setAllNamesAndPaths($graph);
        //ToDo: remove this loop
        foreach ($graph->getSpecifications() as $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operation) {
                if($operation->getRequest() !== null) {
                    $request = $operation->getRequest();

                    foreach ($request->getProperties() as $property) {
                        $object = $property->getObjectTypeDefinition();
                        if($object === null) continue;

                        $this->nameGenerator->setTreePathsAndClassNames($object, $request->getNamespace(), substr($request->getClassName(), 0 , -3).$object->getClassName(), $request->getFilePath());
                    }

                }
            }
        }

        $this->eventDispatcher->dispatch(new ClassGraphReadyEvent($graph));

        $files = $this->filesGenerator->generateAllFiles($graph);

        $this->eventDispatcher->dispatch(new FilesReadyEvent($files));

        foreach ($files as $item) {
            $this->writer->write($item->getClass()->getFilePath(), $item->getClass()->getFileName(), $item->getFileContents());
        }
    }
}
