<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

use cebe\openapi\spec\Parameter;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\BaseDefinition;

class RequestParametersDtoDefinition extends BaseDefinition
{
    /**
     * @var Parameter[] $parameters
     * @psalm-var list<Parameter> $parameters
     */
    private array $parameters;

    public function __construct(
        string $directoryPath,
        string $fileName,
        string $namespace,
        string $className,
        Parameter ...$parameters
    ) {
        parent::__construct($directoryPath, $fileName, $namespace, $className);

        $this->parameters = $parameters;
    }

    /**
     * @return Parameter[]
     *
     * @psalm-return list<Parameter>
     */
    public function parameters() : array
    {
        return $this->parameters;
    }

    public function setParameters(Parameter ...$parameters) : void
    {
        $this->parameters = $parameters;
    }
}
