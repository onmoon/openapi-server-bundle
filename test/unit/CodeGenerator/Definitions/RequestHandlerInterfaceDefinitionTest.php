<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/** @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition */
final class RequestHandlerInterfaceDefinitionTest extends TestCase
{
    /** @return mixed[] */
    public static function requestHandlerInterfaceDefinitionProvider(): array
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
        $payload['requestType']   = (bool) $conditions['hasRequestType'] ? new DtoDefinition([]) : null;
        $payload['responseTypes'] = (bool) $conditions['hasResponseType'] ? [new DtoDefinition([])] : [];

        $requestHandlerInterfaceDefinition = new RequestHandlerInterfaceDefinition($payload['requestType'], $payload['responseTypes']);
        $requestHandlerInterfaceDefinition
            ->setMethodName($payload['methodName'])
            ->setMethodDescription($payload['methodDescription']);

        Assert::assertSame($payload['requestType'], $requestHandlerInterfaceDefinition->getRequestType());
        Assert::assertSame($payload['responseTypes'], $requestHandlerInterfaceDefinition->getResponseTypes());
        Assert::assertSame($payload['methodName'], $requestHandlerInterfaceDefinition->getMethodName());
        Assert::assertSame($payload['methodDescription'], $requestHandlerInterfaceDefinition->getMethodDescription());
        Assert::assertSame(RequestHandler::class, $requestHandlerInterfaceDefinition->getExtends()->getFQCN());
    }
}
