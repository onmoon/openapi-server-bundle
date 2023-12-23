<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\AttributeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ComponentDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use function array_map;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\AttributeGenerator
 */
class AttributeGeneratorTest extends TestCase
{
    private Property $property;
    private Property $propertyTwo;
    private Property $propertyThree;

    private PropertyDefinition $propertyDefinition;
    private PropertyDefinition $propertyDefinitionTwo;
    private PropertyDefinition $propertyDefinitionThree;

    private DtoDefinition $requestDtoDefinition;
    private ResponseDefinition $responseDtoDefinition;

    private OperationDefinition $operationDefinition;
    private GraphDefinition $graphDefinition;

    private AttributeGenerator $attributesGenerator;

    private RequestHandlerInterfaceDefinition $requestHandlerInterface;

    private ComponentDefinition $componentDefinition;

    public function setUp(): void
    {
        $this->property      = new Property('testOne');
        $this->propertyTwo   = new Property('testTwo');
        $this->propertyThree = new Property('testThree');

        $propertyObjectTypeDefinition      = new DtoDefinition([]);
        $propertyObjectTypeDefinitionTwo   = new DtoDefinition([]);
        $propertyObjectTypeDefinitionThree = new DtoDefinition([]);

        $this->propertyDefinition = new PropertyDefinition($this->property);
        $this->propertyDefinition->setObjectTypeDefinition($propertyObjectTypeDefinition);

        $this->propertyDefinitionTwo = new PropertyDefinition($this->propertyTwo);
        $this->propertyDefinitionTwo->setObjectTypeDefinition($propertyObjectTypeDefinitionTwo);

        $this->propertyDefinitionThree = new PropertyDefinition($this->propertyThree);
        $this->propertyDefinitionThree->setObjectTypeDefinition($propertyObjectTypeDefinitionThree);

        $this->requestDtoDefinition  = new DtoDefinition([$this->propertyDefinition]);
        $this->responseDtoDefinition = new ResponseDefinition('200', new DtoDefinition([$this->propertyDefinitionTwo]));

        $this->requestHandlerInterface = new RequestHandlerInterfaceDefinition(
            $this->requestDtoDefinition,
            array_map(
                static fn (ResponseDefinition $response) => $response->getResponseBody(),
                [$this->responseDtoDefinition]
            )
        );

        $this->operationDefinition = new OperationDefinition(
            '/',
            'get',
            'test',
            '',
            null,
            null,
            $this->requestDtoDefinition,
            [$this->responseDtoDefinition],
            $this->requestHandlerInterface
        );

        $this->componentDefinition = new ComponentDefinition('TestComponent');
        $this->componentDefinition->setDto(new DtoDefinition([$this->propertyDefinitionThree]));

        $this->graphDefinition = new GraphDefinition(
            [
                new SpecificationDefinition(
                    new SpecificationConfig('/', null, '/', 'application/json'),
                    [$this->operationDefinition],
                    [$this->componentDefinition]
                ),
            ],
            new ServiceSubscriberDefinition()
        );

        $this->attributesGenerator = new AttributeGenerator();
    }

    public function testSetAllAttributesDefaultCall(): void
    {
        $this->property->setRequired(false);
        $this->property->setDefaultValue('testDefault');

        $this->attributesGenerator->setAllAttributes($this->graphDefinition);

        Assert::assertFalse($this->property->isRequired());
        Assert::assertEquals('testDefault', $this->property->getDefaultValue());
        Assert::assertFalse($this->property->isNullable());

        Assert::assertTrue($this->propertyDefinition->hasGetter());
        Assert::assertFalse($this->propertyDefinition->hasSetter());
        Assert::assertFalse($this->propertyDefinition->isNullable());
        Assert::assertFalse($this->propertyDefinition->isInConstructor());

        Assert::assertFalse($this->propertyTwo->isRequired());
        Assert::assertFalse($this->propertyTwo->isNullable());

        Assert::assertTrue($this->propertyDefinitionTwo->hasGetter());
        Assert::assertTrue($this->propertyDefinitionTwo->hasSetter());
        Assert::assertTrue($this->propertyDefinitionTwo->isNullable());
        Assert::assertFalse($this->propertyDefinitionTwo->isInConstructor());

        Assert::assertFalse($this->propertyThree->isRequired());
        Assert::assertFalse($this->propertyThree->isNullable());

        Assert::assertTrue($this->propertyDefinitionThree->hasGetter());
        Assert::assertTrue($this->propertyDefinitionThree->hasSetter());
        Assert::assertTrue($this->propertyDefinitionThree->isNullable());
        Assert::assertFalse($this->propertyDefinitionThree->isInConstructor());
    }

    public function testSetAllAttributesCaseTwo(): void
    {
        $this->propertyTwo->setRequired(true);
        $this->propertyTwo->setDefaultValue(null);

        $this->attributesGenerator->setAllAttributes($this->graphDefinition);

        Assert::assertTrue($this->propertyDefinitionTwo->hasGetter());
        Assert::assertFalse($this->propertyDefinitionTwo->hasSetter());
        Assert::assertFalse($this->propertyDefinitionTwo->isNullable());
        Assert::assertTrue($this->propertyDefinitionTwo->isInConstructor());
    }

    public function testSetAllAttributesCaseOne(): void
    {
        $this->propertyDefinition->setHasGetter(false);
        $this->propertyDefinition->setHasSetter(true);
        $this->propertyDefinition->setNullable(false);

        $this->operationDefinition = new OperationDefinition(
            '/',
            'get',
            'test',
            '',
            null,
            null,
            null,
            [$this->responseDtoDefinition],
            $this->requestHandlerInterface
        );

        $this->graphDefinition = new GraphDefinition(
            [
                new SpecificationDefinition(
                    new SpecificationConfig('/', null, '/', 'application/json'),
                    [$this->operationDefinition],
                    [$this->componentDefinition]
                ),
            ],
            new ServiceSubscriberDefinition()
        );
        $this->attributesGenerator->setAllAttributes($this->graphDefinition);

        Assert::assertFalse($this->propertyDefinition->hasGetter());
        Assert::assertTrue($this->propertyDefinition->hasSetter());
        Assert::assertFalse($this->propertyDefinition->isNullable());
        Assert::assertFalse($this->propertyDefinition->isInConstructor());
    }

    public function testRequestPassDefault(): void
    {
        $property = new Property('first');
        $property->setNullable(true);
        $property->setRequired(true);
        $property->setDefaultValue('test');

        $propertyTwo = new Property('two');
        $propertyTwo->setNullable(false);
        $propertyTwo->setRequired(false);
        $propertyTwo->setDefaultValue(null);

        $propertyDefinition    = new PropertyDefinition($property);
        $propertyDefinitionTwo = new PropertyDefinition($propertyTwo);
        $root                  = new DtoDefinition([$propertyDefinition, $propertyDefinitionTwo]);

        $attributesGenerator = new AttributeGenerator();
        $attributesGenerator->requestPass($root);

        Assert::assertTrue($propertyDefinition->hasGetter());
        Assert::assertFalse($propertyDefinition->hasSetter());
        Assert::assertFalse($propertyDefinition->isInConstructor());
        Assert::assertTrue($propertyDefinition->isNullable());

        Assert::assertTrue($propertyDefinitionTwo->hasGetter());
        Assert::assertFalse($propertyDefinitionTwo->hasSetter());
        Assert::assertFalse($propertyDefinitionTwo->isInConstructor());
        Assert::assertTrue($propertyDefinitionTwo->isNullable());
    }

    public function testRequestPassWithNestedObject(): void
    {
        $property = new Property('first');
        $property->setNullable(true);
        $property->setRequired(true);
        $property->setDefaultValue('test');

        $propertyDefinition    = new PropertyDefinition($property);
        $propertyDefinitionTwo = new PropertyDefinition($property);

        $root      = new DtoDefinition([$propertyDefinition]);
        $secondary = new DtoDefinition([$propertyDefinitionTwo]);
        $propertyDefinition->setObjectTypeDefinition($secondary);

        $attributesGenerator = new AttributeGenerator();
        $attributesGenerator->requestPass($root);

        Assert::assertTrue($propertyDefinition->hasGetter());
        Assert::assertFalse($propertyDefinition->hasSetter());
        Assert::assertFalse($propertyDefinition->isInConstructor());
        Assert::assertTrue($propertyDefinition->isNullable());
    }

    public function testResponsePassDefault(): void
    {
        $property = new Property('first');
        $property->setNullable(true);
        $property->setRequired(true);
        $property->setDefaultValue('test');

        $propertyTwo = new Property('two');
        $propertyTwo->setNullable(true);
        $propertyTwo->setRequired(true);
        $propertyTwo->setDefaultValue('testTwo');

        $propertyDefinition    = new PropertyDefinition($property);
        $propertyDefinitionTwo = new PropertyDefinition($propertyTwo);
        $root                  = new DtoDefinition([$propertyDefinition, $propertyDefinitionTwo]);

        $attributesGenerator = new AttributeGenerator();
        $attributesGenerator->responsePass($root);

        Assert::assertTrue($propertyDefinition->hasGetter());
        Assert::assertTrue($propertyDefinition->hasSetter());
        Assert::assertTrue($propertyDefinition->isNullable());
        Assert::assertFalse($propertyDefinition->isInConstructor());

        Assert::assertTrue($propertyDefinitionTwo->hasGetter());
        Assert::assertTrue($propertyDefinitionTwo->hasSetter());
        Assert::assertFalse($propertyDefinitionTwo->isInConstructor());
        Assert::assertTrue($propertyDefinitionTwo->isNullable());
    }

    public function testResponsePassWithNestedObject(): void
    {
        $property = new Property('first');
        $property->setNullable(true);
        $property->setRequired(true);
        $property->setDefaultValue('test');

        $propertyTwo = new Property('two');
        $propertyTwo->setNullable(true);
        $propertyTwo->setRequired(true);
        $propertyTwo->setDefaultValue('testTwo');

        $propertyDefinition    = new PropertyDefinition($property);
        $propertyDefinitionTwo = new PropertyDefinition($propertyTwo);

        $root      = new DtoDefinition([$propertyDefinition]);
        $secondary = new DtoDefinition([$propertyDefinitionTwo]);
        $propertyDefinition->setObjectTypeDefinition($secondary);

        $attributesGenerator = new AttributeGenerator();
        $attributesGenerator->responsePass($root);

        Assert::assertTrue($propertyDefinitionTwo->hasGetter());
        Assert::assertTrue($propertyDefinitionTwo->hasSetter());
        Assert::assertTrue($propertyDefinitionTwo->isNullable());
        Assert::assertFalse($propertyDefinitionTwo->isInConstructor());
    }

    public function testComponentPassDefault(): void
    {
        $this->propertyThree
            ->setRequired(false)
            ->setDefaultValue('testValue')
            ->setNullable(true);

        $attributesGenerator = new AttributeGenerator();
        $attributesGenerator->componentsPass($this->componentDefinition->getDto());

        Assert::assertFalse($this->propertyThree->isRequired());
        Assert::assertSame('testValue', $this->propertyThree->getDefaultValue());
        Assert::assertTrue($this->propertyThree->isNullable());

        Assert::assertTrue($this->propertyDefinitionThree->hasGetter());
        Assert::assertTrue($this->propertyDefinitionThree->hasSetter());
        Assert::assertTrue($this->propertyDefinitionThree->isNullable());
        Assert::assertFalse($this->propertyDefinitionThree->isInConstructor());
    }

    public function testComponentPassCaseTwo(): void
    {
        $this->propertyThree
            ->setRequired(true)
            ->setDefaultValue(null)
            ->setNullable(false);

        $attributesGenerator = new AttributeGenerator();
        $attributesGenerator->componentsPass($this->componentDefinition->getDto());

        Assert::assertTrue($this->propertyThree->isRequired());
        Assert::assertSame(null, $this->propertyThree->getDefaultValue());
        Assert::assertFalse($this->propertyThree->isNullable());

        Assert::assertTrue($this->propertyDefinitionThree->hasGetter());
        Assert::assertFalse($this->propertyDefinitionThree->hasSetter());
        Assert::assertFalse($this->propertyDefinitionThree->isNullable());
        Assert::assertTrue($this->propertyDefinitionThree->isInConstructor());
    }
}
