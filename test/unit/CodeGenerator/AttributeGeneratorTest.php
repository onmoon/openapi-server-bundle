<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\AttributeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\AttributeGenerator
 */
class AttributeGeneratorTest extends TestCase
{
    private Property $property;
    private Property $propertyTwo;

    private PropertyDefinition $propertyDefinition;
    private PropertyDefinition $propertyDefinitionTwo;

    private RequestDtoDefinition $requestDtoDefinition;
    private ResponseDtoDefinition $responseDtoDefinition;

    private OperationDefinition $operationDefinition;
    private GraphDefinition $graphDefinition;

    private AttributeGenerator $attributesGenerator;

    public function setUp(): void
    {
        $this->property    = new Property('testOne');
        $this->propertyTwo = new Property('testTwo');

        $propertyObjectTypeDefinition    = new DtoDefinition([]);
        $propertyObjectTypeDefinitionTwo = new DtoDefinition([]);

        $this->propertyDefinition = new PropertyDefinition($this->property);
        $this->propertyDefinition->setObjectTypeDefinition($propertyObjectTypeDefinition);

        $this->propertyDefinitionTwo = new PropertyDefinition($this->propertyTwo);
        $this->propertyDefinitionTwo->setObjectTypeDefinition($propertyObjectTypeDefinitionTwo);

        $requestBodyDtoDefinition   = new RequestBodyDtoDefinition([$this->propertyDefinition]);
        $this->requestDtoDefinition = new RequestDtoDefinition($requestBodyDtoDefinition, null, null);

        $this->responseDtoDefinition = new ResponseDtoDefinition('200', [$this->propertyDefinitionTwo]);
        $this->operationDefinition   = new OperationDefinition(
            '/',
            'get',
            'test',
            '',
            null,
            $this->requestDtoDefinition,
            [$this->responseDtoDefinition]
        );
        $this->graphDefinition       = new GraphDefinition(
            [
                new SpecificationDefinition(
                    new SpecificationConfig('/', null, '/', 'application/json'),
                    [$this->operationDefinition]
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

        Assert::assertTrue($this->propertyDefinitionTwo->hasGetter());
        Assert::assertTrue($this->propertyDefinitionTwo->hasSetter());
        Assert::assertTrue($this->propertyDefinitionTwo->isNullable());
        Assert::assertFalse($this->propertyDefinitionTwo->isInConstructor());
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
            [$this->responseDtoDefinition]
        );
        $this->graphDefinition     = new GraphDefinition(
            [
                new SpecificationDefinition(
                    new SpecificationConfig('/', null, '/', 'application/json'),
                    [$this->operationDefinition]
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
        $specProperty = new Property('first');
        $specProperty->setNullable(true);
        $specProperty->setRequired(true);
        $specProperty->setDefaultValue('test');

        $specPropertyTwo = new Property('two');
        $specPropertyTwo->setNullable(true);
        $specPropertyTwo->setRequired(true);
        $specPropertyTwo->setDefaultValue('testTwo');

        $property          = new PropertyDefinition($specProperty);
        $secondaryProperty = new PropertyDefinition($specPropertyTwo);
        $root              = new DtoDefinition([$property, $secondaryProperty]);

        $attributesGenerator = new AttributeGenerator();
        $attributesGenerator->requestPass($root);

        Assert::assertTrue($property->hasGetter());
        Assert::assertFalse($property->hasSetter());
        Assert::assertFalse($property->isInConstructor());
        Assert::assertTrue($property->isNullable());

        Assert::assertTrue($secondaryProperty->hasGetter());
        Assert::assertFalse($secondaryProperty->hasSetter());
        Assert::assertFalse($secondaryProperty->isInConstructor());
        Assert::assertTrue($secondaryProperty->isNullable());
    }

    public function testRequestPassWithNestedObject(): void
    {
        $specProperty = new Property('first');
        $specProperty->setNullable(true);
        $specProperty->setRequired(true);
        $specProperty->setDefaultValue('test');

        $property          = new PropertyDefinition($specProperty);
        $secondaryProperty = new PropertyDefinition($specProperty);

        $root      = new DtoDefinition([$property]);
        $secondary = new DtoDefinition([$secondaryProperty]);
        $property->setObjectTypeDefinition($secondary);

        $attributesGenerator = new AttributeGenerator();
        $attributesGenerator->requestPass($root);

        Assert::assertTrue($property->hasGetter());
        Assert::assertFalse($property->hasSetter());
        Assert::assertFalse($property->isInConstructor());
        Assert::assertTrue($property->isNullable());
    }

    public function testResponsePassDefault(): void
    {
        $specProperty = new Property('first');
        $specProperty->setNullable(true);
        $specProperty->setRequired(true);
        $specProperty->setDefaultValue('test');

        $specPropertyTwo = new Property('two');
        $specPropertyTwo->setNullable(true);
        $specPropertyTwo->setRequired(true);
        $specPropertyTwo->setDefaultValue('testTwo');

        $property          = new PropertyDefinition($specProperty);
        $secondaryProperty = new PropertyDefinition($specPropertyTwo);
        $root              = new DtoDefinition([$property, $secondaryProperty]);

        $attributesGenerator = new AttributeGenerator();
        $attributesGenerator->responsePass($root);

        Assert::assertTrue($property->hasGetter());
        Assert::assertTrue($property->hasSetter());
        Assert::assertTrue($property->isNullable());
        Assert::assertFalse($property->isInConstructor());

        Assert::assertTrue($secondaryProperty->hasGetter());
        Assert::assertTrue($secondaryProperty->hasSetter());
        Assert::assertFalse($secondaryProperty->isInConstructor());
        Assert::assertTrue($secondaryProperty->isNullable());
    }

    public function testResponsePassWithNestedObject(): void
    {
        $specProperty = new Property('first');
        $specProperty->setNullable(true);
        $specProperty->setRequired(true);
        $specProperty->setDefaultValue('test');

        $specPropertyTwo = new Property('two');
        $specPropertyTwo->setNullable(true);
        $specPropertyTwo->setRequired(true);
        $specPropertyTwo->setDefaultValue('testTwo');

        $property          = new PropertyDefinition($specProperty);
        $secondaryProperty = new PropertyDefinition($specPropertyTwo);

        $root      = new DtoDefinition([$property]);
        $secondary = new DtoDefinition([$secondaryProperty]);
        $property->setObjectTypeDefinition($secondary);

        $attributesGenerator = new AttributeGenerator();
        $attributesGenerator->responsePass($root);

        Assert::assertTrue($secondaryProperty->hasGetter());
        Assert::assertTrue($secondaryProperty->hasSetter());
        Assert::assertTrue($secondaryProperty->isNullable());
        Assert::assertFalse($secondaryProperty->isInConstructor());
    }
}
