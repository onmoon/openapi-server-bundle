<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Validator;

use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\RoutedServerRequestValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use OnMoon\OpenApiServerBundle\Validator\LeaguePSR7RequestSchemaValidator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;

class LeaguePSR7RequestSchemaValidatorTest extends TestCase
{
    public function testValidate(): void
    {
        $operationId = 'test';
        $openApi     = new OpenApi([]);
        $request     = new Request();

        $operation = $this->createMock(Operation::class);
        $operation
            ->expects(self::once())
            ->method('getUrl');
        $operation
            ->expects(self::once())
            ->method('getMethod');

        $specification = $this->createMock(Specification::class);
        $specification
            ->expects(self::once())
            ->method('getOperation')
            ->with($operationId)
            ->willReturn($operation);

        $specification
            ->expects(self::once())
            ->method('getOpenApi')
            ->willReturn($openApi);

        $validatorBuilderMock = $this->createMock(ValidatorBuilder::class);
        $validatorBuilderMock
            ->expects(self::once())
            ->method('fromSchema')
            ->willReturn($validatorBuilderMock);

        $routedServerRequestValidator = $this->createMock(RoutedServerRequestValidator::class);

        $validatorBuilderMock
            ->expects(self::once())
            ->method('getRoutedRequestValidator')
            ->willReturn($routedServerRequestValidator);

        $serverRequestInterfaceStub = $this->createStub(ServerRequestInterface::class);

        $psrHttpFactory = $this->createMock(PsrHttpFactory::class);
        $psrHttpFactory
            ->expects(self::once())
            ->method('createRequest')
            ->with($request)
            ->willReturn($serverRequestInterfaceStub);

        $routedServerRequestValidator
            ->expects(self::once())
            ->method('validate');

        $leaguePSR7RequestSchemaValidator = new LeaguePSR7RequestSchemaValidator($validatorBuilderMock, $psrHttpFactory);
        $leaguePSR7RequestSchemaValidator->validate($request, $specification, $operationId);
    }
}
