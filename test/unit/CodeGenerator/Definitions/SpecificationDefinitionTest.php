<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ComponentDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use function str_replace;

use const DIRECTORY_SEPARATOR;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition
 */
final class SpecificationDefinitionTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function specificationDefinitionProvider(): array
    {
        return [
            [
                'conditions' => ['hasOperations' => false],
            ],
            [
                'conditions' => ['hasOperations' => true],
            ],
        ];
    }

    /**
     * @param mixed[] $conditions
     *
     * @dataProvider specificationDefinitionProvider
     */
    public function testSpecificationDefinition(array $conditions): void
    {
        $payload = [
            'url' => '/some/custom/relative/url',
            'method' => 'GET',
            'operationId' => '',
            'requestHandlerName' => 'SomeCustomRequestHandlerName',
            'summary' => null,
            'request' => null,
            'responses' => [],
        ];

        $specificationConfig = new SpecificationConfig(
            str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/path'),
            null,
            'Some\Namespace',
            'some/media-type'
        );

        $requestHandlerInterfaceDefinition = new RequestHandlerInterfaceDefinition(null, []);

        $operationDefinition = new OperationDefinition(
            $payload['url'],
            $payload['method'],
            $payload['operationId'],
            $payload['requestHandlerName'],
            $payload['summary'],
            null,
            $payload['request'],
            $payload['responses'],
            $requestHandlerInterfaceDefinition
        );

        $payload                  = [];
        $payload['specification'] = $specificationConfig;
        $payload['operations']    = (bool) $conditions['hasOperations'] ? [$operationDefinition] : [];

        $specificationDefinition = new SpecificationDefinition(
            $payload['specification'],
            $payload['operations'],
            [new ComponentDefinition('TestComponent')]
        );

        Assert::assertSame($payload['operations'], $specificationDefinition->getOperations());
        Assert::assertSame($payload['specification'], $specificationDefinition->getSpecification());
    }
}
