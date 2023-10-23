<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

final class SpecificationConfig
{
    private string $path;
    private ?string $type;
    private string $nameSpace;
    private string $mediaType;
    private ?string $dateTimeClass;

    public function __construct(string $path, ?string $type, string $nameSpace, string $mediaType, ?string $dateTimeClass = null)
    {
        $this->path          = $path;
        $this->type          = $type;
        $this->nameSpace     = $nameSpace;
        $this->mediaType     = $mediaType;
        $this->dateTimeClass = $dateTimeClass;
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

    public function getDateTimeClass(): ?string
    {
        return $this->dateTimeClass;
    }
}
