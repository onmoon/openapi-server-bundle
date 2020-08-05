<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition
 */
final class OperationDefinitionTest extends TestCase
{
    /** @var RequestDtoDefinition|MockObject $requestDtoDefinitionMock */
    private $requestDtoDefinitionMock;

    /** @var ResponseDtoDefinition|MockObject $responseDtoDefinitionMock */
    private $responseDtoDefinitionMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->requestDtoDefinitionMock  = $this->createMock(RequestDtoDefinition::class);
        $this->responseDtoDefinitionMock = $this->createMock(ResponseDtoDefinition::class);
    }

    protected function tearDown(): void
    {
        unset(
            $this->requestDtoDefinitionMock,
            $this->responseDtoDefinitionMock
        );

        parent::tearDown();
    }

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
                    'hasRequestDtoDefinitionMock' => false,
                    'hasResponseDtoDefinitionMock' => false,
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
                    'hasRequestDtoDefinitionMock' => true,
                    'hasResponseDtoDefinitionMock' => true,
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
        $payload['request']   = (bool) $conditions['hasRequestDtoDefinitionMock'] ? $this->requestDtoDefinitionMock : null;
        $payload['responses'] = (bool) $conditions['hasResponseDtoDefinitionMock'] ? [$this->responseDtoDefinitionMock] : [];

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
        /** @var ClassDefinition|MockObject $classDefinitionMock */
        $classDefinitionMock = $this->createMock(ClassDefinition::class);

        /** @var RequestHandlerInterfaceDefinition|MockObject $requestHandlerInterfaceMock */
        $requestHandlerInterfaceMock = $this->createMock(RequestHandlerInterfaceDefinition::class);

        /** @var ClassDefinition|MockObject $changedClassDefinitionMock */
        $changedClassDefinitionMock = $this->createMock(ClassDefinition::class);

        /** @var RequestHandlerInterfaceDefinition|MockObject $changedRequestHandlerInterfaceMock */
        $changedRequestHandlerInterfaceMock = $this->createMock(RequestHandlerInterfaceDefinition::class);

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

        $generatedInterfaceDefinition->setMarkersInterface($classDefinitionMock);
        $generatedInterfaceDefinition->setRequestHandlerInterface($requestHandlerInterfaceMock);

        Assert::assertSame($classDefinitionMock, $generatedInterfaceDefinition->getMarkersInterface());
        Assert::assertSame($requestHandlerInterfaceMock, $generatedInterfaceDefinition->getRequestHandlerInterface());

        $generatedInterfaceDefinition
            ->setMarkersInterface($changedClassDefinitionMock)
            ->setRequestHandlerInterface($changedRequestHandlerInterfaceMock);

        Assert::assertSame($changedClassDefinitionMock, $generatedInterfaceDefinition->getMarkersInterface());
        Assert::assertSame($changedRequestHandlerInterfaceMock, $generatedInterfaceDefinition->getRequestHandlerInterface());
    }
}
