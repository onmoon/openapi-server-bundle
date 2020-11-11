<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition
 */
final class OperationDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function operationDefinitionProvider(): array
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
            ? new RequestDtoDefinition()
            : null;
        $payload['responses'] = (bool) $conditions['hasResponseDtoDefinition']
            ? [new ResponseDtoDefinition('200', [])]
            : [];

        $generatedInterfaceDefinition = new OperationDefinition(
            $payload['url'],
            $payload['method'],
            $payload['operationId'],
            $payload['requestHandlerName'],
            $payload['summary'],
            $payload['request'],
            $payload['responses']
        );

        Assert::assertSame($payload['url'], $generatedInterfaceDefinition->getUrl());
        Assert::assertSame($payload['method'], $generatedInterfaceDefinition->getMethod());
        Assert::assertSame($payload['operationId'], $generatedInterfaceDefinition->getOperationId());
        Assert::assertSame($payload['requestHandlerName'], $generatedInterfaceDefinition->getRequestHandlerName());
        Assert::assertSame($payload['summary'], $generatedInterfaceDefinition->getSummary());
        Assert::assertSame($payload['request'], $generatedInterfaceDefinition->getRequest());
        Assert::assertSame($payload['responses'], $generatedInterfaceDefinition->getResponses());
    }

    public function testOperationDefinitionChanged(): void
    {
        $classDefinition                = new ClassDefinition();
        $requestHandlerInterface        = new RequestHandlerInterfaceDefinition();
        $changedClassDefinition         = new ClassDefinition();
        $changedRequestHandlerInterface = new RequestHandlerInterfaceDefinition();

        $payload = [
            'url' => '/some/custom/relative/url',
            'method' => 'GET',
            'operationId' => '',
            'requestHandlerName' => 'SomeCustomRequestHandlerName',
            'summary' => null,
            'request' => null,
            'responses' => [],
        ];

        $generatedInterfaceDefinition = new OperationDefinition(
            $payload['url'],
            $payload['method'],
            $payload['operationId'],
            $payload['requestHandlerName'],
            $payload['summary'],
            $payload['request'],
            $payload['responses']
        );

        $generatedInterfaceDefinition->setMarkersInterface($classDefinition);
        $generatedInterfaceDefinition->setRequestHandlerInterface($requestHandlerInterface);

        Assert::assertSame($classDefinition, $generatedInterfaceDefinition->getMarkersInterface());
        Assert::assertSame($requestHandlerInterface, $generatedInterfaceDefinition->getRequestHandlerInterface());

        $generatedInterfaceDefinition
            ->setMarkersInterface($changedClassDefinition)
            ->setRequestHandlerInterface($changedRequestHandlerInterface);

        Assert::assertSame($changedClassDefinition, $generatedInterfaceDefinition->getMarkersInterface());
        Assert::assertSame($changedRequestHandlerInterface, $generatedInterfaceDefinition->getRequestHandlerInterface());
    }
}
