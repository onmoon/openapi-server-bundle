<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition
 */
final class RequestHandlerInterfaceDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function requestHandlerInterfaceDefinitionProvider(): array
    {
        return [
            [
                'payload' => [
                    'methodName' => 'CustomMethod',
                    'methodDescription' => null,
                ],
                'conditions' => [
                    'hasRequestType' => false,
                    'hasResponseType' => false,
                ],
            ],
            [
                'payload' => [
                    'methodName' => 'CustomMethod',
                    'methodDescription' => 'Custom Method Description',
                ],
                'conditions' => [
                    'hasRequestType' => true,
                    'hasResponseType' => true,
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $payload
     * @param mixed[] $conditions
     *
     * @dataProvider requestHandlerInterfaceDefinitionProvider
     */
    public function testRequestHandlerInterfaceDefinition(array $payload, array $conditions): void
    {
        /** @var ClassDefinition|MockObject $requestTypeClassDefinitionMock */
        $requestTypeClassDefinitionMock = $this->createMock(ClassDefinition::class);
        /** @var ClassDefinition|MockObject $responseTypeClassDefinitionMock */
        $responseTypeClassDefinitionMock = $this->createMock(ClassDefinition::class);

        $payload['requestType']  = (bool) $conditions['hasRequestType'] ? $requestTypeClassDefinitionMock : null;
        $payload['responseType'] = (bool) $conditions['hasResponseType'] ? $responseTypeClassDefinitionMock : null;

        $requestHandlerInterfaceDefinition = new RequestHandlerInterfaceDefinition();
        $requestHandlerInterfaceDefinition
            ->setRequestType($payload['requestType'])
            ->setResponseType($payload['responseType'])
            ->setMethodName($payload['methodName'])
            ->setMethodDescription($payload['methodDescription']);

        Assert::assertSame($payload['requestType'], $requestHandlerInterfaceDefinition->getRequestType());
        Assert::assertSame($payload['responseType'], $requestHandlerInterfaceDefinition->getResponseType());
        Assert::assertSame($payload['methodName'], $requestHandlerInterfaceDefinition->getMethodName());
        Assert::assertSame($payload['methodDescription'], $requestHandlerInterfaceDefinition->getMethodDescription());
    }
}
