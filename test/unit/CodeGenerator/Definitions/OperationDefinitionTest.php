<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/** @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition */
final class OperationDefinitionTest extends TestCase
{
    /** @return mixed[] */
    public static function operationDefinitionProvider(): array
    {
        return [
            [
                'payload' => [
                    'url' => '/some/custom/relative/url',
                    'method' => 'GET',
                    'operationId' => '',
                    'requestHandlerName' => 'SomeCustomRequestHandlerName',
                    'summary' => null,
                ],
                'conditions' => [
                    'hasRequestDtoDefinition' => false,
                    'hasResponseDtoDefinition' => false,
                ],
            ],
            [
                'payload' => [
                    'url' => '/some/custom/relative/url',
                    'method' => 'GET',
                    'operationId' => '',
                    'requestHandlerName' => 'SomeCustomRequestHandlerName',
                    'summary' => 'SomeCustomSummary',
                ],
                'conditions' => [
                    'hasRequestDtoDefinition' => true,
                    'hasResponseDtoDefinition' => true,
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $payload
     * @param mixed[] $conditions
     *
     * @dataProvider operationDefinitionProvider
     */
    public function testOperationDefinition(array $payload, array $conditions): void
    {
        $payload['request']   = (bool) $conditions['hasRequestDtoDefinition']
            ? new DtoDefinition([])
            : null;
        $payload['responses'] = (bool) $conditions['hasResponseDtoDefinition']
            ? [new ResponseDefinition('200', new DtoDefinition([]))]
            : [];

        $requestHandlerInterface = new RequestHandlerInterfaceDefinition(
            $payload['request'],
            (bool) $conditions['hasResponseDtoDefinition'] ? [$payload['responses'][0]->getResponseBody()] : []
        );

        $generatedInterfaceDefinition = new OperationDefinition(
            $payload['url'],
            $payload['method'],
            $payload['operationId'],
            $payload['requestHandlerName'],
            $payload['summary'],
            null,
            $payload['request'],
            $payload['responses'],
            $requestHandlerInterface
        );

        Assert::assertSame($payload['url'], $generatedInterfaceDefinition->getUrl());
        Assert::assertSame($payload['method'], $generatedInterfaceDefinition->getMethod());
        Assert::assertSame($payload['operationId'], $generatedInterfaceDefinition->getOperationId());
        Assert::assertSame($payload['requestHandlerName'], $generatedInterfaceDefinition->getRequestHandlerName());
        Assert::assertSame($payload['summary'], $generatedInterfaceDefinition->getSummary());
        Assert::assertSame($payload['request'], $generatedInterfaceDefinition->getRequest());
        Assert::assertSame($payload['responses'], $generatedInterfaceDefinition->getResponses());
        Assert::assertSame($requestHandlerInterface, $generatedInterfaceDefinition->getRequestHandlerInterface());
    }
}
