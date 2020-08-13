<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Specification;

use cebe\openapi\spec\OpenApi;
use Exception;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Specification\SpecificationParser;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function array_pop;
use function Safe\sprintf;

/**
 * @covers \OnMoon\OpenApiServerBundle\Specification\SpecificationLoader
 */
class SpecificationLoaderTest extends TestCase
{
    /** @var SpecificationParser|MockObject $specificationParser */
    private $specificationParser;
    /** @var FileLocatorInterface|MockObject $fileLocator  */
    private $fileLocator;
    /** @var mixed $cache  */
    private $cache;

    public const SPECIFICATION_NAME = 'cards';

    public function setUp(): void
    {
        $this->specificationParser = $this->createMock(SpecificationParser::class);
        $this->fileLocator         = $this->createMock(FileLocatorInterface::class);
        $this->cache               = new class () implements TagAwareCacheInterface {
            /** @var Specification[] $items  */
            private array $items = [];

            /**
             * {@inheritDoc}
             */
            public function get(string $key, callable $callback, ?float $beta = null, ?array &$metadata = null)
            {
                if (isset($this->items[$key])) {
                    return $this->items[$key];
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
                $this->items[$key] = $item;

                return $item;
            }

            /**
             * @return array|Specification[]
             */
            public function getCachedItems(): array
            {
                return $this->items;
            }

            public function set(string $key, Specification $item): void
            {
                $this->items[$key] = $item;
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
        };

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->specificationParser, $this->fileLocator, $this->cache);
    }

    public function testRegisterSpecAndGettingRegisteredSpecifications(): void
    {
        $specificationLoader         = new SpecificationLoader(
            new SpecificationParser(new ScalarTypesResolver()),
            $this->fileLocator,
            $this->cache
        );
        $specificationFileName       = 'specification.json';
        $specificationArray          = $this->getSpecificationArray($specificationFileName);
        $expectedSpecificationConfig = new SpecificationConfig(
            $specificationArray['path'],
            $specificationArray['type'] ?? null,
            $specificationArray['name_space'],
            $specificationArray['media_type']
        );

        $expectedSpecifications = [self::SPECIFICATION_NAME => $expectedSpecificationConfig];

        $specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $specifications = $specificationLoader->list();

        Assert::assertEquals($expectedSpecifications, $specifications);
    }

    public function testGetThrowsException(): void
    {
        $specificationName   = 'test';
        $specificationLoader = new SpecificationLoader(
            $this->specificationParser,
            $this->fileLocator,
            $this->cache
        );

        $specificationFileName = 'specification.json';
        $specificationArray    = $this->getSpecificationArray($specificationFileName);
        $specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);

        /**
         * phpcs:disable SlevomatCodingStandard.Exceptions.ReferenceThrowableOnly
         */
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf('OpenApi spec "%s" is not registered in bundle config, ' .
            'Registered specs are: %s.', $specificationName, self::SPECIFICATION_NAME));

        $specificationLoader->get($specificationName);
    }

    public function testGetReturnsSpecification(): void
    {
        $specificationLoader         = new SpecificationLoader(
            $this->specificationParser,
            $this->fileLocator,
            $this->cache
        );
        $specificationFileName       = 'specification.json';
        $specificationArray          = $this->getSpecificationArray($specificationFileName);
        $expectedSpecificationConfig = new SpecificationConfig(
            $specificationArray['path'],
            $specificationArray['type'] ?? null,
            $specificationArray['name_space'],
            $specificationArray['media_type']
        );

        $specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);

        $specificationConfig = $specificationLoader->get(self::SPECIFICATION_NAME);
        Assert::assertEquals($expectedSpecificationConfig, $specificationConfig);
    }

    public function testLoadMoreThenOneSpecificationFilePathThrowsException(): void
    {
        $specificationFileName = 'test1';
        $this->fileLocator->expects(self::once())
            ->method('locate')
            ->willReturn(['test1', 'test2']);
        $specificationLoader = new SpecificationLoader(
            $this->specificationParser,
            $this->fileLocator,
            $this->cache
        );
        $specificationArray  = $this->getSpecificationArray($specificationFileName);

        $specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf('More than one file path found for specification "%s".', __DIR__ . '/' . $specificationFileName));

        $specificationLoader->load(self::SPECIFICATION_NAME);
    }

    public function testLoadNotLocalSpecificationFileThrowsException(): void
    {
        $specificationFileName = 'https://missed_specification.spec';
        $this->fileLocator->expects(self::once())
            ->method('locate')
            ->willReturn($specificationFileName);
        $specificationLoader = new SpecificationLoader(
            $this->specificationParser,
            $this->fileLocator,
            $this->cache
        );
        $specificationArray  = $this->getSpecificationArray($specificationFileName);

        $specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf('This is not a local file "%s".', $specificationFileName));

        $specificationLoader->load(self::SPECIFICATION_NAME);
    }

    public function testLoadMissedSpecificationFileThrowsException(): void
    {
        $specificationFileName = 'missed_specification.spec';
        $this->fileLocator->expects(self::once())
            ->method('locate')
            ->willReturn($specificationFileName);
        $specificationLoader = new SpecificationLoader(
            $this->specificationParser,
            $this->fileLocator,
            $this->cache
        );
        $specificationArray  = $this->getSpecificationArray($specificationFileName);

        $specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf('File "%s" not found', $specificationFileName));

        $specificationLoader->load(self::SPECIFICATION_NAME);
    }

    public function testLoadSavesSpecificationInCache(): void
    {
        $this->specificationParser
            ->expects(self::once())
            ->method('parseOpenApi')
            ->willReturn(new Specification([], new OpenApi([])));

        $specificationLoader   = new SpecificationLoader(
            $this->specificationParser,
            new FileLocator(),
            $this->cache
        );
        $specificationFileName = 'specification.yaml';
        $specificationArray    = $this->getSpecificationArray($specificationFileName);

        $specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $specification        = $specificationLoader->load(self::SPECIFICATION_NAME);
        $cachedSpecifications = $this->cache->getCachedItems();
        Assert::assertCount(1, $cachedSpecifications);
        $specificationFromCache = array_pop($cachedSpecifications);
        Assert::assertSame($specification, $specificationFromCache);
    }

    public function testLoadFetchesSpecificationFromCache(): void
    {
        $this->specificationParser
        ->expects(self::never())
        ->method('parseOpenApi');

        $specificationLoader   = new SpecificationLoader(
            $this->specificationParser,
            new FileLocator(),
            $this->cache
        );
        $specificationFileName = 'specification.yaml';
        $specificationArray    = $this->getSpecificationArray($specificationFileName);

        $specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $cacheKey            = 'openapi-server-bundle-specification-' . self::SPECIFICATION_NAME;
        $cachedSpecification = new Specification([], new OpenApi([]));
        $this->cache->set($cacheKey, $cachedSpecification);

        $loadedSpecification = $specificationLoader->load(self::SPECIFICATION_NAME);

        Assert::assertSame($loadedSpecification, $cachedSpecification);
    }

    public function testLoadForbiddenSpecificationTypeThrowsException(): void
    {
        $specificationLoader   = new SpecificationLoader(
            $this->specificationParser,
            new FileLocator(),
            $this->cache
        );
        $specificationFileName = 'specification.xml';
        $specificationArray    = $this->getSpecificationArray($specificationFileName);

        $specificationLoader->registerSpec(self::SPECIFICATION_NAME, $specificationArray);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf('Failed to determine spec type for "%s".
                    Try specifying "type" parameter in bundle config with either "yaml" or "json" value', __DIR__ . '/' . $specificationFileName));
        $specificationLoader->load(self::SPECIFICATION_NAME);
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
