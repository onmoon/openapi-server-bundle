<?php


namespace OnMoon\OpenApiServerBundle\Specification;


class Specification
{
    private string $path;
    private ?string $type;
    private string $nameSpace;
    private string $mediaType;

    /**
     * Specification constructor.
     * @param string $path
     * @param string|null $type
     * @param string $nameSpace
     * @param string $mediaType
     */
    public function __construct(string $path, ?string $type, string $nameSpace, string $mediaType)
    {
        $this->path = $path;
        $this->type = $type;
        $this->nameSpace = $nameSpace;
        $this->mediaType = $mediaType;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getNameSpace(): string
    {
        return $this->nameSpace;
    }

    /**
     * @return string
     */
    public function getMediaType(): string
    {
        return $this->mediaType;
    }


}