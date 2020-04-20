<?php


namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;


use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The FilesReadyEvent event occurs after all class files
 * are generated before they are written to files.
 *
 * This event allows you to modify generated files content,
 * e.g. change code style.
 */
class FilesReadyEvent extends Event
{
    /** @var GeneratedFileDefinition[] */
    private array $files;

    /**
     * FilesReadyEvent constructor.
     * @param array|GeneratedFileDefinition[] $files
     */
    public function __construct($files)
    {
        $this->files = $files;
    }

    /**
     * @return GeneratedFileDefinition[]
     */
    public function files(): array
    {
        return $this->files;
    }

}