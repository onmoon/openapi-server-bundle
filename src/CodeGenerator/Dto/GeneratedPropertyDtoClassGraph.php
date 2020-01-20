<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto;

use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;

final class GeneratedPropertyDtoClassGraph
{
    private string $import;
    private string $type;
    /** @var GeneratedClass[] */
    private array $classGraph;

    /**
     * @param GeneratedClass[] $classGraph
     */
    public function __construct(string $import, string $type, array $classGraph)
    {
        $this->import     = $import;
        $this->type       = $type;
        $this->classGraph = $classGraph;
    }

    public function getImport() : string
    {
        return $this->import;
    }

    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @return GeneratedClass[]
     */
    public function getClassGraph() : array
    {
        return $this->classGraph;
    }
}
