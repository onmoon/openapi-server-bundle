<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

class SpecificationConfig
{
    private string $path;
    private ?string $type;
    private string $nameSpace;
    private string $mediaType;

    public function __construct(string $path, ?string $type, string $nameSpace, string $mediaType)
    {
        $this->path      = $path;
        $this->type      = $type;
        $this->nameSpace = $nameSpace;
        $this->mediaType = $mediaType;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getNameSpace(): string
    {
        return $this->nameSpace;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }
}
