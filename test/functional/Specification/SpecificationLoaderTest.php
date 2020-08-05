<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Specification;

use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Specification\SpecificationParser;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Throwable;

use function Safe\rename;
use function Safe\sprintf;

/**
 * @covers \OnMoon\OpenApiServerBundle\Specification\SpecificationLoader
 */
class SpecificationLoaderTest extends TestCase
{
    protected SpecificationLoader $specificationLoader;

    public const SPECIFICATION_NAME                    = 'cards';
    public const SPECIFICATION_LOADER_CACHE_KEY_PREFIX = 'openapi-server-bundle-specification-';

    public function setUp(): void
    {
        $this->specificationLoader = new SpecificationLoader(
            new SpecificationParser(new ScalarTypesResolver()),
            new FileLocator(),
            new class () implements TagAwareCacheInterface {
                /** @var Specification[] $specs  */
                private array $specs = [];

                /**
                 * {@inheritDoc}
                 */
                public function get(string $key, callable $callback, ?float $beta = null, ?array &$metadata = null)
                {
                    Assert::assertSame(SpecificationLoaderTest::SPECIFICATION_LOADER_CACHE_KEY_PREFIX . SpecificationLoaderTest::SPECIFICATION_NAME, $key);
                    if (isset($this->specs[$key])) {
                        return $this->specs[$key];
                    }

                    $item              = $callback(new class () implements ItemInterface {
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
                            Assert::assertSame(SpecificationLoader::CACHE_TAG, $tags);

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
                    $this->specs[$key] = $item;

                    return $item;
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

        parent::setUp();
    }

    public function testRegisterSpecAndGettingRegisteredSpecification(): void
    {
        $specificationFileName       = 'specification.json';
        $specificationArray          = $this->getSpecificationArray($specificationFileName);
        $expectedSpecificationConfig = new SpecificationConfig(
            $specificationArray['path'],
            $specificationArray['type'] ?? null,
            $specificationArray['name_space'],
            $specificationArray['media_type']
        );

        $expectedSpecifications = [self::SPECIFICATION_NAME => $expectedSpecificationConfig];

        $this->specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $specifications = $this->specificationLoader->list();

        Assert::assertEquals($expectedSpecifications, $specifications);

        $specificationConfig = $this->specificationLoader->get(self::SPECIFICATION_NAME);
        Assert::assertEquals($expectedSpecificationConfig, $specificationConfig);
    }

    public function testLoadYamlFromCache(): void
    {
        $specificationFileName = 'specification.yaml';
        $specificationArray    = $this->getSpecificationArray($specificationFileName);

        $this->specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $specification = $this->specificationLoader->load(self::SPECIFICATION_NAME);

        rename(__DIR__ . '/' . $specificationFileName, __DIR__ . '/' . $specificationFileName . '_test');
        $specificationFromCache = $this->specificationLoader->load(self::SPECIFICATION_NAME);
        Assert::assertSame($specification, $specificationFromCache);
        rename(__DIR__ . '/' . $specificationFileName . '_test', __DIR__ . '/' . $specificationFileName);
    }

    public function testLoadJsonFromCache(): void
    {
        $specificationFileName = 'specification.json';
        $specificationArray    = $this->getSpecificationArray($specificationFileName);

        $this->specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $specification = $this->specificationLoader->load(self::SPECIFICATION_NAME);

        rename(__DIR__ . '/' . $specificationFileName, __DIR__ . '/' . $specificationFileName . '_test');
        $specificationFromCache = $this->specificationLoader->load(self::SPECIFICATION_NAME);
        Assert::assertSame($specification, $specificationFromCache);
        rename(__DIR__ . '/' . $specificationFileName . '_test', __DIR__ . '/' . $specificationFileName);
    }

    public function testLoadMissedSpecificationFileThrowsException(): void
    {
        $specificationFileName = 'specification.spec';
        $specificationArray    = $this->getSpecificationArray($specificationFileName);

        $this->specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage(sprintf('The file "%s" does not exist.', __DIR__ . '/' . $specificationFileName));
        $this->specificationLoader->load(self::SPECIFICATION_NAME);
    }

    public function testLoadForbiddenSpecificationTypeThrowsException(): void
    {
        $specificationFileName = 'specification.xml';
        $specificationArray    = $this->getSpecificationArray($specificationFileName);

        $this->specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $this->expectException(Throwable::class);
        $this->specificationLoader->load(self::SPECIFICATION_NAME);
    }

    /**
     * @return  array{path:string,type:string|null,name_space:string,media_type:string} $spec
     */
    private function getSpecificationArray(string $specificationFileName): array
    {
        return [
            'path' => __DIR__ . '/' . $specificationFileName,
            'type' => null,
            'name_space' => 'CardsApi',
            'media_type' => 'application/json',
        ];
    }
}
