<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Validator;

use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
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
        Specification $specification,
        string $operationId
    ) : void {
        $operation = $specification->getOperations()[$operationId];
        $this->validatorBuilder
            ->fromSchema($specification->getOpenApi())
            ->getRoutedRequestValidator()
            ->validate(
                new OperationAddress($operation->getUrl(), $operation->getMethod()),
                $this->httpFactory->createRequest($request)
            );
    }
}
