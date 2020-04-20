<?php


namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The ClassGraphReadyEvent event occurs after all specifications
 * has been parsed and graph of classes to be generated has been
 * constructed.
 *
 * This event allows you to modify:
 * * Class names, namespaces and paths,
 * * Property attributes, getters and setters,
 * * Base interfaces and classes.
 */
class ClassGraphReadyEvent extends Event
{
    private GraphDefinition $graph;

    /**
     * ClassGraphReadyEvent constructor.
     * @param GraphDefinition $graph
     */
    public function __construct(GraphDefinition $graph)
    {
        $this->graph = $graph;
    }

    /**
     * @return GraphDefinition
     */
    public function graph(): GraphDefinition
    {
        return $this->graph;
    }

}