<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Specification\Definitions;

use cebe\openapi\spec\OpenApi;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectSchema;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use function count;

/**
 * @covers \OnMoon\OpenApiServerBundle\Specification\Definitions\Specification
 */
final class SpecificationTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function specificationsProvider(): array
    {
        $operationData = [
            'url' => '/some/custom/path',
            'method' => 'POST',
            'requestHandlerName' => 'some-custom-request-handler-name',
            'summary' => 'Some Custom Summary',
            'requestBody' => new ObjectSchema([]),
            'requestParameters' => [
                'query' => new ObjectSchema([]),
                'path' => new ObjectSchema([]),
            ],
            'responses' => [],
        ];

        return [
            [
                'specificationData' => [
                    'operations' => [],
                ],
            ],
            [
                'specificationData' => [
                    'operations' => [
                        'customOperationId' => new Operation(
                            $operationData['url'],
                            $operationData['method'],
                            $operationData['requestHandlerName'],
                            $operationData['summary'],
                            $operationData['requestBody'],
                            $operationData['requestParameters'],
                            $operationData['responses']
                        ),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $specificationData
     *
     * @dataProvider specificationsProvider
     */
    public function testSpecifications(array $specificationData): void
    {
        $openApiMock = $this->createMock(OpenApi::class);

        $specification = new Specification(
            $specificationData['operations'],
            $openApiMock
        );

        Assert::assertSame($specification->getOperations(), $specificationData['operations']);
        Assert::assertSame($specification->getOpenApi(), $openApiMock);

        if (count($specification->getOperations()) === 0) {
            return;
        }

        foreach ($specificationData['operations'] as $operationId => $operationData) {
            Assert::assertSame($specification->getOperation($operationId), $operationData);
        }
    }
}
