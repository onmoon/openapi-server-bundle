<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Generation\Types;

use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
use OnMoon\OpenApiServerBundle\Test\Generation\GeneratedClassAsserter;
use OnMoon\OpenApiServerBundle\Test\Generation\GenerationTestCase;
use Psr\Container\ContainerInterface;

final class TypesGenerationTest extends GenerationTestCase
{
    public function testGetTestOKDto(): void
    {
        $generatedFiles = $this->generateCodeFromSpec(__DIR__ . '/specification.yaml');

        $okDtoAsserter = new GeneratedClassAsserter(
            $generatedFiles,
            '/test/Apis/TestApi/GetTest/Dto/Response/OK/GetTestOKDto.php',
        );

        $okDtoAsserter->assertInNamespace('Test\Apis\TestApi\GetTest\Dto\Response\OK');
        $okDtoAsserter->assertHasName('GetTestOKDto');
        $okDtoAsserter->assertImplements(ResponseDto::class);
        $okDtoAsserter->assertHasProperty('string_property', 'string', false);
        $okDtoAsserter->assertHasUseStatement('OnMoon\OpenApiServerBundle\Interfaces\ResponseDto');
        $okDtoAsserter->assertHasMethod('getStringProperty');
        $okDtoAsserter->assertMethodDocblockContains('toArray', '/** @inheritDoc */');
        $okDtoAsserter->assertMethodReturns('getStringProperty', 'string', false);
        $okDtoAsserter->assertMethodHasArgument('__construct', 'string_property', 'string', false);
    }

    public function testGetTest(): void
    {
        $generatedFiles = $this->generateCodeFromSpec(__DIR__ . '/specification.yaml');

        $getTestAsserter = new GeneratedClassAsserter(
            $generatedFiles,
            '/test/Apis/TestApi/GetTest/GetTest.php',
        );

        $getTestAsserter->assertExtends(RequestHandler::class);
        $getTestAsserter->assertHasMethod('getTest');
        $getTestAsserter->assertMethodReturns('getTest', 'Test\Apis\TestApi\GetTest\Dto\Response\OK\GetTestOKDto', false);
    }

    public function testApiServiceLoaderServiceSubscriber(): void
    {
        $generatedFiles = $this->generateCodeFromSpec(__DIR__ . '/specification.yaml');

        $subscriberServiceAsserter = new GeneratedClassAsserter(
            $generatedFiles,
            '/test/ServiceSubscriber/ApiServiceLoaderServiceSubscriber.php',
        );

        $subscriberServiceAsserter->assertMethodHasArgument('__construct', 'locator', ContainerInterface::class, false);
    }
}
