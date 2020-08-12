<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\InterfaceGenerator;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\InterfaceGenerator
 */
class InterfaceGeneratorTest extends TestCase
{
    public function testSetAllInterfacesForOneResponseSetsResponse(): void
    {
        $request                      = null;
        $propertyObjectTypeDefinition = new DtoDefinition([]);
        $propertyDefinition           = new PropertyDefinition(new Property(''));
        $propertyDefinition->setObjectTypeDefinition($propertyObjectTypeDefinition);
        $responseDtoDefinition = new ResponseDtoDefinition('200', [$propertyDefinition]);
        $operationDefinition   = new OperationDefinition(
            '/',
            'get',
            'test',
            '',
            null,
            $request,
            [$responseDtoDefinition]
        );
        $graphDefinition       = new GraphDefinition(
            [
                new SpecificationDefinition(
                    new SpecificationConfig('/', null, '/', 'application/json'),
                    [$operationDefinition]
                ),
            ],
            new ServiceSubscriberDefinition()
        );

        $interfaceGenerator = new InterfaceGenerator();
        $interfaceGenerator->setAllInterfaces($graphDefinition);

        Assert::assertNull($operationDefinition->getMarkersInterface());

        $expectedResponseImplements = ClassDefinition::fromFQCN(ResponseDto::class);
        $responseImplements         = $responseDtoDefinition->getImplements();
        Assert::assertEquals($expectedResponseImplements, $responseImplements);

        $expectedRequestHandler = new RequestHandlerInterfaceDefinition();
        $expectedRequestHandler
            ->setResponseType($responseDtoDefinition)
            ->setRequestType($request)
            ->setExtends(ClassDefinition::fromFQCN(RequestHandler::class));
        $requestHandler = $operationDefinition->getRequestHandlerInterface();
        Assert::assertEquals($expectedRequestHandler, $requestHandler);

        $expectedPropertyObjectTypeDefinitionImplements = ClassDefinition::fromFQCN(Dto::class);
        $propertyObjectTypeDefinitionImplements         = $propertyObjectTypeDefinition->getImplements();
        Assert::assertEquals($expectedPropertyObjectTypeDefinitionImplements, $propertyObjectTypeDefinitionImplements);
    }

    public function testSetAllInterfacesForSeveralResponsesSetsResponse(): void
    {
        $request                     = null;
        $responseDtoDefinitionFirst  = new ResponseDtoDefinition('200', []);
        $responseDtoDefinitionSecond = new ResponseDtoDefinition('304', []);
        $operationDefinition         = new OperationDefinition(
            '/',
            'get',
            'test',
            '',
            null,
            $request,
            [$responseDtoDefinitionFirst, $responseDtoDefinitionSecond]
        );
        $graphDefinition             = new GraphDefinition(
            [
                new SpecificationDefinition(
                    new SpecificationConfig('/', null, '/', 'application/json'),
                    [$operationDefinition]
                ),
            ],
            new ServiceSubscriberDefinition()
        );

        $interfaceGenerator = new InterfaceGenerator();
        $interfaceGenerator->setAllInterfaces($graphDefinition);

        $expectedMakersInterface = new GeneratedInterfaceDefinition();
        $expectedMakersInterface->setExtends(ClassDefinition::fromFQCN(ResponseDto::class));
        $makerInterface = $operationDefinition->getMarkersInterface();
        Assert::assertEquals($expectedMakersInterface, $makerInterface);

        $expectedResponseDtoDefinitionImplements = $expectedMakersInterface;
        Assert::assertEquals($expectedResponseDtoDefinitionImplements, $responseDtoDefinitionFirst->getImplements());
        Assert::assertEquals($expectedResponseDtoDefinitionImplements, $responseDtoDefinitionSecond->getImplements());

        $expectedRequestHandler = new RequestHandlerInterfaceDefinition();
        $expectedRequestHandler
            ->setResponseType($expectedMakersInterface)
            ->setRequestType($request)
            ->setExtends(ClassDefinition::fromFQCN(RequestHandler::class));
        $requestHandler = $operationDefinition->getRequestHandlerInterface();
        Assert::assertEquals($expectedRequestHandler, $requestHandler);
    }

    public function testSetAllInterfacesWithRequestSetsRequest(): void
    {
        $requestBodyDtoDefinition = new RequestBodyDtoDefinition([]);
        $request                  = new RequestDtoDefinition($requestBodyDtoDefinition, null, null);
        $responseDtoDefinition    = new ResponseDtoDefinition('200', []);
        $operationDefinition      = new OperationDefinition(
            '/',
            'get',
            'test',
            '',
            null,
            $request,
            [$responseDtoDefinition]
        );
        $graphDefinition          = new GraphDefinition(
            [
                new SpecificationDefinition(
                    new SpecificationConfig('/', null, '/', 'application/json'),
                    [$operationDefinition]
                ),
            ],
            new ServiceSubscriberDefinition()
        );

        $interfaceGenerator = new InterfaceGenerator();
        $interfaceGenerator->setAllInterfaces($graphDefinition);

        $expectedRequestImplements = ClassDefinition::fromFQCN(Dto::class);
        $requestImplements         = $request->getImplements();
        Assert::assertEquals($expectedRequestImplements, $requestImplements);

        $expectedRequestBodyDtoDefinitionImplements = ClassDefinition::fromFQCN(Dto::class);
        $requestBodyDtoDefinitionImplements         = $requestBodyDtoDefinition->getImplements();
        Assert::assertEquals($expectedRequestBodyDtoDefinitionImplements, $requestBodyDtoDefinitionImplements);
    }
}
