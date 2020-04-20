<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FileWriter;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\ClassGraphReadyEvent;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\FilesReadyEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ApiServerCodeGenerator
{
    private GraphGenerator $graphGenerator;
    private NameGenerator $nameGenerator;
    private InterfaceGenerator $interfaceGenerator;
    private FileGenerator $filesGenerator;
    private AttributeGenerator $attributeGenerator;
    private FileWriter $writer;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(GraphGenerator $graphGenerator, NameGenerator $nameGenerator, InterfaceGenerator $interfaceGenerator, FileGenerator $filesGenerator, AttributeGenerator $attributeGenerator, FileWriter $writer, EventDispatcherInterface $eventDispatcher)
    {
        $this->graphGenerator     = $graphGenerator;
        $this->nameGenerator      = $nameGenerator;
        $this->interfaceGenerator = $interfaceGenerator;
        $this->filesGenerator     = $filesGenerator;
        $this->attributeGenerator = $attributeGenerator;
        $this->writer             = $writer;
        $this->eventDispatcher    = $eventDispatcher;
    }

    public function generate() : void
    {
        $graph = $this->graphGenerator->generateClassGraph();
        $this->interfaceGenerator->setAllInterfaces($graph);
        $this->attributeGenerator->setAllAttributes($graph);
        $this->nameGenerator->setAllNamesAndPaths($graph);

        $this->eventDispatcher->dispatch(new ClassGraphReadyEvent($graph));

        $files = $this->filesGenerator->generateAllFiles($graph);

        $this->eventDispatcher->dispatch(new FilesReadyEvent($files));

        foreach ($files as $item) {
            $this->writer->write($item->getClass()->getFilePath(), $item->getClass()->getFileName(), $item->getFileContents());
        }
    }
}
