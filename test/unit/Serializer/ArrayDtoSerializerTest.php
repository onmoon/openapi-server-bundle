<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Serializer;

use OnMoon\OpenApiServerBundle\Apis\Example\PostWithPath\Dto\Request\PostWithPathRequestDto;
use OnMoon\OpenApiServerBundle\Apis\Example\PostWithPath\Dto\Response\OK\PostWithPathOKDto;
use OnMoon\OpenApiServerBundle\Serializer\ArrayDtoSerializer;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * @covers \OnMoon\OpenApiServerBundle\Serializer\ArrayDtoSerializer
 */
class ArrayDtoSerializerTest extends TestCase
{
    /** @var ScalarTypesResolver|MockObject */
    private $resolverMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->resolverMock = $this->createMock(ScalarTypesResolver::class);
    }

    public function tearDown(): void
    {
        unset($this->resolverMock);

        parent::tearDown();
    }

    /**
     * @return mixed[]
     */
    public function createRequestDtoWithPathProvider(): array
    {
        $queryProperties               = [];
        $queryProperties['queryParam'] = new Property('queryParam');
        $queryProperties['queryParam']->setDefaultValue('e55e57d3b65ff510b257025746ffb6e1');

        $query = new ObjectType($queryProperties);

        $pathProperties              = [];
        $pathProperties['pathParam'] = new Property('pathParam');
        $pathProperties['pathParam']->setDefaultValue('e55e57d3b65ff510b257025746ffb6e1');

        $path = new ObjectType($pathProperties);

        $bodyProperties         = [];
        $bodyProperties['name'] = new Property('name');
        $bodyProperties['name']->setDefaultValue('bodyName');
        $bodyProperties['value'] = new Property('value');
        $bodyProperties['value']->setDefaultValue('bodyValue');

        $requestBody = new ObjectType($bodyProperties);

        return [
            [
                'requestQuery' => $query,
                'requestPath'  => $path,
                'requestBody' => $requestBody,
            ],
        ];
    }

    /**
     * @throws Throwable
     *
     * @dataProvider createRequestDtoWithPathProvider
     */
    public function testCreateRequestDtoWithPath(ObjectType $requestQuery, ObjectType $requestPath, ObjectType $requestBody): void
    {
        $request = new Request(
            [],
            [],
            ['_route_params' => (object) ['customParam' => 'customParam']],
            [],
            [],
            [],
            '{}'
        );

        $operation = new Operation(
            '/example/path',
            'POST',
            'PostRequest',
            null,
            $requestBody,
            ['query' => $requestQuery, 'path' => $requestPath],
            []
        );

        $this->resolverMock
            ->method('convert')
            ->willReturn(null);

        $arrayDtoSerializer = new ArrayDtoSerializer(
            $this->resolverMock,
            true
        );

        /** @var PostWithPathRequestDto $requestDto */
        $requestDto = $arrayDtoSerializer->createRequestDto(
            $request,
            $operation,
            PostWithPathRequestDto::class
        );

        Assert::assertSame(
            $requestQuery->getProperties()['queryParam']->getDefaultValue(),
            $requestDto->getQueryParameters()->getQueryParam()
        );
        Assert::assertSame(
            $requestPath->getProperties()['pathParam']->getDefaultValue(),
            $requestDto->getPathParameters()->getPathParam()
        );
        Assert::assertSame(
            $requestBody->getProperties()['name']->getDefaultValue(),
            $requestDto->getBody()->getName()
        );
        Assert::assertSame(
            $requestBody->getProperties()['value']->getDefaultValue(),
            $requestDto->getBody()->getValue()
        );
    }

    public function testCreateResponseFromDto(): void
    {
        $responseDto = PostWithPathOKDto::fromArray(['result' => 'e55e57d3b65ff510b257025746ffb6e1']);

        $responseProperties           = [];
        $responseProperties['result'] = new Property('result');
        $responseProperties['result']->setDefaultValue('e55e57d3b65ff510b257025746ffb6e1');

        $responseParameters = [
            PostWithPathOKDto::_getResponseCode() => new ObjectType($responseProperties),
        ];

        $operation = new Operation(
            '/example/path',
            'POST',
            'PostRequest',
            null,
            null,
            [],
            $responseParameters
        );

        $this->resolverMock
            ->method('convert')
            ->willReturn(null);

        $arrayDtoSerializer = new ArrayDtoSerializer(
            $this->resolverMock,
            true
        );

        $response = $arrayDtoSerializer->createResponseFromDto(
            $responseDto,
            $operation
        );

        Assert::assertNull($response['result']);
    }
}
