<?php


namespace OnMoon\OpenApiServerBundle\Specification;


use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use Symfony\Component\Config\FileLocatorInterface;

class SpecificationLoader
{
    private array $specs = [];
    private FileLocatorInterface $locator;

    /**
     * SpecificationLoader constructor.
     * @param FileLocatorInterface $locator
     */
    public function __construct(FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    public function registerSpec($name, array $spec) {
        $this->specs[$name] = new Specification(
            $spec['path'],
            $spec['type']??null,
            $spec['name_space'],
            $spec['media_type']
        );
    }

    /**
     * @return Specification[]
     */
    public function list() {
        return $this->specs;
    }

    public function get(string $name): Specification {
        if(empty($this->specs[$name]))
            throw new \Exception('OpenApi spec "'.$name.'" is not registered in bundle config, '.
                'Registered specs are: '.implode(', ', array_keys($this->specs)).'.');

        return $this->specs[$name];
    }

    public function load($name): OpenApi {
        $spec = $this->get($name);

        $specPath = $this->locator->locate($spec->getPath());

        if (!stream_is_local($specPath)) {
            throw new \Exception(sprintf('This is not a local file "%s".', $specPath));
        }

        if (!file_exists($specPath)) {
            throw new \Exception(sprintf('File "%s" not found.', $specPath));
        }


        $type = $spec->getType();
        if(is_null($type)) {
            $type = pathinfo($specPath, PATHINFO_EXTENSION);
        }

        if($type === 'yaml') {
            $openApi = Reader::readFromYamlFile($specPath);
        } elseif ($type === 'json') {
            $openApi = Reader::readFromJsonFile($specPath);
        } else {
            throw new \Exception('Failed to determine spec type for "'.$specPath.'". '.
                'Try specifying "type" parameter in bundle config with either "yaml" or "json" value');
        }

        if (is_null($openApi)) {
            throw new \Exception(sprintf('File "%s" is could not be loaded.', $specPath));
        }

        return $openApi;
    }
}
