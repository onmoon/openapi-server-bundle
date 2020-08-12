<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Validator;

use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\RoutedServerRequestValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use OnMoon\OpenApiServerBundle\Validator\LeaguePSR7RequestSchemaValidator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \OnMoon\OpenApiServerBundle\Validator\LeaguePSR7RequestSchemaValidator
 */
final class LeaguePSR7RequestSchemaValidatorTest extends TestCase
{
    public function testValidate(): void
    {
        $operationId = 'test';
        $openApi     = new OpenApi([]);
        $request     = new Request();

        $operation     = new Operation('test_url', 'test_method', 'RequestHandler');
        $specification = new Specification([$operationId => $operation], $openApi);

        $validatorBuilderMock = $this->createMock(ValidatorBuilder::class);
        $validatorBuilderMock
            ->expects(self::once())
            ->method('fromSchema')
            ->with($openApi)
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
            ->method('validate')
            ->with(new OperationAddress('test_url', 'test_method'));

        $leaguePSR7RequestSchemaValidator = new LeaguePSR7RequestSchemaValidator($validatorBuilderMock, $psrHttpFactory);
        $leaguePSR7RequestSchemaValidator->validate($request, $specification, $operationId);
    }
}
