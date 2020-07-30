<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Generation;

use Lukasoppermann\Httpstatus\Httpstatus;
use OnMoon\OpenApiServerBundle\CodeGenerator\ApiServerCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\AttributeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\FileGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FileWriter;
use OnMoon\OpenApiServerBundle\CodeGenerator\GraphGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\InterfaceGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\NameGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\DefaultNamingStrategy;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\DtoCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\InterfaceCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\ServiceSubscriberCodeGenerator;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Specification\SpecificationParser;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PhpParser\BuilderFactory;
use sspat\ReservedWords\ReservedWords;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class TestApiServerCodeGeneratorFactory
{
    /**
     * @param mixed[] $specifications
     */
    public static function getCodeGenerator(
        array $specifications,
        FileWriter $fileWriter,
        string $rootNamespace = 'Test\\',
        string $rootPath = '/test',
        string $languageLevel = '7.4',
        bool $fullDocs = true
    ): ApiServerCodeGenerator {
        $builderFactory     = new BuilderFactory();
        $scalarTypeResolver = new ScalarTypesResolver();

        $specificationLoader = new SpecificationLoader(
            new SpecificationParser(
                new ScalarTypesResolver()
            ),
            new FileLocator(),
            new class () implements TagAwareCacheInterface {
                /**
                 * {@inheritDoc}
                 */
                public function get(string $key, callable $callback, ?float $beta = null, ?array &$metadata = null)
                {
                    return $callback(new class () implements ItemInterface {
                        /**
                         * {@inheritDoc}
                         */
                        public function getKey()
                        {
                            return '';
                        }

                        /**
                         * {@inheritDoc}
                         */
                        public function set($value)
                        {
                            return $this;
                        }

                        /**
                         * {@inheritDoc}
                         */
                        public function expiresAfter($time)
                        {
                            return $this;
                        }

                        /**
                         * {@inheritDoc}
                         */
                        public function isHit()
                        {
                            return true;
                        }

                        /**
                         * {@inheritDoc}
                         */
                        public function get()
                        {
                            return null;
                        }

                        /**
                         * {@inheritDoc}
                         */
                        public function getMetadata(): array
                        {
                            return [];
                        }

                        /**
                         * {@inheritDoc}
                         */
                        public function tag($tags): ItemInterface
                        {
                            return $this;
                        }

                        /**
                         * {@inheritDoc}
                         */
                        public function expiresAt($expiration)
                        {
                            return $this;
                        }
                    });
                }

                /**
                 * {@inheritDoc}
                 */
                public function invalidateTags(array $tags)
                {
                    return true;
                }

                public function delete(string $key): bool
                {
                    return true;
                }
            }
        );

        foreach ($specifications as $specificationName => $specification) {
            $specificationLoader->registerSpec($specificationName, $specification);
        }

        return new ApiServerCodeGenerator(
            new GraphGenerator($specificationLoader),
            new NameGenerator(
                new DefaultNamingStrategy(
                    new ReservedWords(),
                    $rootNamespace,
                    $rootPath
                ),
                new Httpstatus(),
                $rootNamespace,
                $rootPath
            ),
            new InterfaceGenerator(),
            new FileGenerator(
                new DtoCodeGenerator(
                    $builderFactory,
                    $scalarTypeResolver,
                    $languageLevel,
                    $fullDocs
                ),
                new InterfaceCodeGenerator(
                    $builderFactory,
                    $scalarTypeResolver,
                    $languageLevel,
                    $fullDocs
                ),
                new ServiceSubscriberCodeGenerator(
                    $builderFactory,
                    $scalarTypeResolver,
                    $languageLevel,
                    $fullDocs
                )
            ),
            new AttributeGenerator(),
            $fileWriter,
            new EventDispatcher()
        );
    }
}
