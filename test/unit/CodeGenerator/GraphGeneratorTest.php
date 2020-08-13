<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestParametersDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\GraphGenerator;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
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

class GraphGeneratorTest extends TestCase
{
    private SpecificationLoader $specificationLoader;

    public function setUp(): void
    {
        $this->specificationLoader = new SpecificationLoader(
            new SpecificationParser(
                new ScalarTypesResolver()
            ),
            new FileLocator(),
            new class () implements TagAwareCacheInterface {
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
            }
        );
    }

    public function tearDown(): void
    {
        unset($this->specificationLoader);
        parent::tearDown();
    }

    public function testGenerateClassGraphWithoutSpecifications(): void
    {
        $graphGenerator = new GraphGenerator($this->specificationLoader);

        $expectedGraphDefinition = new GraphDefinition([], new ServiceSubscriberDefinition());
        $graphDefinition         = $graphGenerator->generateClassGraph();

        Assert::assertEquals($expectedGraphDefinition, $graphDefinition);
    }

    public function testGenerateClassGraphWithSpecification(): void
    {
        $specificationPath = __DIR__ . '/specification.yaml';
        $this->specificationLoader->registerSpec('test', ['path' => $specificationPath, 'type'=>null, 'name_space' => '/', 'media_type' => '']);

        $graphGenerator = new GraphGenerator($this->specificationLoader);

        $specificationConfig = new SpecificationConfig(
            $specificationPath,
            null,
            '/',
            ''
        );
        $property            = new Property('goodId');
        $property
            ->setRequired(true)
            ->setScalarTypeId(0)
            ->setDescription('Good ID');
        $requestParametersDtoDefinition = new RequestParametersDtoDefinition([new PropertyDefinition($property)]);
        $requestDtoDefinition           = new RequestDtoDefinition(null, null, $requestParametersDtoDefinition);
        $operationDefinition            = new OperationDefinition('/goods/{goodId}', 'get', 'getGood', 'test.getGood', null, $requestDtoDefinition, []);
        $specificationDefinition        = new SpecificationDefinition($specificationConfig, [$operationDefinition]);
        $graphDefinition                = $graphGenerator->generateClassGraph();
        $expectedGraphDefinition        = new GraphDefinition([$specificationDefinition], new ServiceSubscriberDefinition());

        Assert::assertEquals($expectedGraphDefinition, $graphDefinition);
    }
}
