<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Validator;

use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;

class LeaguePSR7RequestSchemaValidator implements RequestSchemaValidator
{
    private ValidatorBuilder $validatorBuilder;
    private PsrHttpFactory $httpFactory;

    public function __construct(
        ValidatorBuilder $validatorBuilder,
        PsrHttpFactory $httpFactory
    ) {
        $this->validatorBuilder = $validatorBuilder;
        $this->httpFactory      = $httpFactory;
    }

    public function validate(
        Request $request,
        OpenApi $specification,
        string $path,
        string $method
    ) : void {
        $this->validatorBuilder
            ->fromSchema($specification)
            ->getRoutedRequestValidator()
            ->validate(
                new OperationAddress($path, $method),
                $this->httpFactory->createRequest($request)
            );
    }
}
