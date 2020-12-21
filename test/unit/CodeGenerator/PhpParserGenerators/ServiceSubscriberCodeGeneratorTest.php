<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\PhpParserGenerators;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
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

        $requestHandlerInterfaceDefinition = new RequestHandlerInterfaceDefinition();
        $requestHandlerInterfaceDefinition->setNamespace('NamespaceOne\NamespaceTwo');
        $requestHandlerInterfaceDefinition->setClassName('ClassName');

        $request               = new RequestDtoDefinition(
            new RequestBodyDtoDefinition(
                [
                    (new PropertyDefinition(new Property('locator')))
                    ->setObjectTypeDefinition(
                        new DtoDefinition([
                            (new PropertyDefinition(new Property('locator')))
                                ->setObjectTypeDefinition(new DtoDefinition([])),
                        ])
                    ),
                ]
            )
        );
        $responseDtoDefinition = new ResponseDtoDefinition(
            '200',
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
        $operationDefinition   = new OperationDefinition(
            '/',
            'get',
            'test',
            'test',
            null,
            $request,
            [$responseDtoDefinition]
        );
        $operationDefinition->setRequestHandlerInterface($requestHandlerInterfaceDefinition);
        $operationDefinition->setMarkersInterface(ClassDefinition::fromFQCN('NamespaceOne\NamespaceTwo\ClassName'));

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
                    [$operationDefinition]
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
/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
class ClassName implements ClassName, ClassName
{
    private ContainerInterface $locator;
    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }
    /**
     * @inheritDoc
     */
    public static function getSubscribedServices()
    {
        return array('test' => '?' . ClassName::class);
    }
    public function get(string $interface) : ?RequestHandler
    {
        if (!$this->locator->has($interface)) {
            return null;
        }
        return $this->locator->get($interface);
    }
}
EOD;

        self::assertEquals($expectedFileContent, $result->getFileContents());
    }
}
