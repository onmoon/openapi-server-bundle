<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\PhpParserGenerators;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ComponentDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\ServiceSubscriberCodeGenerator;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PhpParser\BuilderFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\ServiceSubscriberCodeGenerator
 */
final class ServiceSubscriberCodeGeneratorTest extends TestCase
{
    private ServiceSubscriberCodeGenerator $serviceSubscriberCodeGenerator;

    public function testGenerateWithValidParams(): void
    {
        $builderFactory     = new BuilderFactory();
        $scalarTypeResolver = new ScalarTypesResolver();

        $this->serviceSubscriberCodeGenerator = new ServiceSubscriberCodeGenerator($builderFactory, $scalarTypeResolver, '1', false);

        $requestHandlerInterfaceDefinition = new RequestHandlerInterfaceDefinition(null, []);
        $requestHandlerInterfaceDefinition->setNamespace('NamespaceOne\NamespaceTwo');
        $requestHandlerInterfaceDefinition->setClassName('ClassName');

        $request = new DtoDefinition(
            [
                (new PropertyDefinition(new Property('locator')))
                ->setObjectTypeDefinition(
                    new DtoDefinition([
                        (new PropertyDefinition(new Property('locator')))
                            ->setObjectTypeDefinition(new DtoDefinition([])),
                    ])
                ),
            ]
        );

        $responseDtoDefinition = new ResponseDefinition(
            '200',
            (new DtoDefinition([
                (new PropertyDefinition(new Property('locator')))
                    ->setObjectTypeDefinition(
                        new DtoDefinition([
                            (new PropertyDefinition(new Property('locator')))
                                ->setObjectTypeDefinition(new DtoDefinition([])),
                        ])
                    ),
            ]))->setNamespace('')->setClassName('ResponceClassName')
        );

        $operationDefinition = new OperationDefinition(
            '/',
            'get',
            'test',
            'test',
            null,
            null,
            $request,
            [$responseDtoDefinition],
            $requestHandlerInterfaceDefinition
        );

        $serviceSubscriberDefinition = new ServiceSubscriberDefinition();
        $serviceSubscriberDefinition->setNamespace('NamespaceOne\NamespaceTwo');
        $serviceSubscriberDefinition->setClassName('ClassName');

        $classDefinitionOne = ClassDefinition::fromFQCN('NamespaceOne\NamespaceTwo\ClassName');
        $classDefinitionTwo = ClassDefinition::fromFQCN('NamespaceOne\NamespaceTwo\ClassName');
        $serviceSubscriberDefinition->setImplements([$classDefinitionOne, $classDefinitionTwo]);

        $graphDefinition = new GraphDefinition(
            [
                new SpecificationDefinition(
                    new SpecificationConfig('/', null, '/', 'application/json'),
                    [$operationDefinition],
                    [new ComponentDefinition('TestComponent')]
                ),
            ],
            $serviceSubscriberDefinition
        );

        $result = $this->serviceSubscriberCodeGenerator->generate($graphDefinition);

        self::assertEquals('NamespaceOne\NamespaceTwo', $result->getClass()->getNamespace());
        self::assertEquals('ClassName', $result->getClass()->getClassName());

        $expectedFileContent = <<<'EOD'
<?php

declare (strict_types=1);
namespace NamespaceOne\NamespaceTwo;

use Psr\Container\ContainerInterface;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use \ResponceClassName;
/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
class ClassName implements ClassName, ClassName
{
    private const HTTP_CODES = [ClassName::class => [ResponceClassName::class => ['200']]];
    private ContainerInterface $locator;
    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }
    /**
     * @inheritDoc
     */
    public static function getSubscribedServices() : array
    {
        return ['test' => '?' . ClassName::class];
    }
    public function get(string $interface) : ?RequestHandler
    {
        if (!$this->locator->has($interface)) {
            return null;
        }
        return $this->locator->get($interface);
    }
    /** @return string[] */
    public function getAllowedCodes(string $apiClass, string $dtoClass) : array
    {
        return self::HTTP_CODES[$apiClass][$dtoClass];
    }
}
EOD;

        self::assertEquals($expectedFileContent, $result->getFileContents());
    }
}
