<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Specification\Definitions;

use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use function count;

/**
 * @covers \OnMoon\OpenApiServerBundle\Specification\Definitions\Operation
 */
class OperationTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function operationsProvider(): array
    {
        return [
            [
                'operationData' => [
                    'url' => '/some/custom/path',
                    'method' => 'GET',
                    'requestHandlerName' => '',
                    'summary' => null,
                    'requestBody' => null,
                    'requestParameters' => [],
                    'responses' => [],
                ],
            ],
            [
                'operationData' => [
                    'url' => '/some/custom/path',
                    'method' => 'POST',
                    'requestHandlerName' => 'some-custom-request-handler-name',
                    'summary' => 'Some Custom Summary',
                    'requestBody' => new ObjectType([]),
                    'requestParameters' => [
                        'query' => new ObjectType([]),
                        'path' => new ObjectType([]),
                    ],
                    'responses' => [
                        '200' => new ObjectType([]),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $operationData
     *
     * @dataProvider operationsProvider
     */
    public function testOperations(array $operationData): void
    {
        $operation = new Operation(
            $operationData['url'],
            $operationData['method'],
            $operationData['requestHandlerName'],
            $operationData['summary'],
            $operationData['requestBody'],
            $operationData['requestParameters'],
            $operationData['responses']
        );

        Assert::assertSame($operation->getUrl(), $operationData['url']);
        Assert::assertSame($operation->getMethod(), $operationData['method']);
        Assert::assertSame($operation->getRequestHandlerName(), $operationData['requestHandlerName']);
        Assert::assertSame($operation->getSummary(), $operationData['summary']);
        Assert::assertSame($operation->getRequestBody(), $operationData['requestBody']);
        Assert::assertSame($operation->getRequestParameters(), $operationData['requestParameters']);
        Assert::assertSame($operation->getResponses(), $operationData['responses']);

        if (count($operation->getResponses()) === 0) {
            return;
        }

        foreach ($operationData['responses'] as $responseCode => $responseData) {
            Assert::assertSame($operation->getResponse((string) $responseCode), $responseData);
        }
    }
}
