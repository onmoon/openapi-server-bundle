<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Types;

use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Types\ArgumentResolver;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @covers \OnMoon\OpenApiServerBundle\Types\ArgumentResolver
 */
class ArgumentResolverTest extends TestCase
{
    private ScalarTypesResolver $typeResolver;
    private Property $pathParameter;

    protected function setUp(): void
    {
        $this->typeResolver  = new ScalarTypesResolver();
        $this->pathParameter = new Property('test');
    }

    public function testResolveArgumentPatternsThrowsException(): void
    {
        $this->pathParameter->setScalarTypeId(null);
        $argumentResolver = new ArgumentResolver($this->typeResolver);
        $pathParameters   = new ObjectType([$this->pathParameter]);
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Object types are not supported in parameters');
        $argumentResolver->resolveArgumentPatterns($pathParameters);
    }

    public function testResolveArgumentPatternsResolvesEmptyPatterns(): void
    {
        $this->pathParameter
            ->setScalarTypeId(0)
            ->setPattern('test_pattern');
        $expectedPatterns = [];
        $pathParameters   = new ObjectType([$this->pathParameter]);
        $argumentResolver = new ArgumentResolver($this->typeResolver);
        $patterns         = $argumentResolver->resolveArgumentPatterns($pathParameters);
        Assert::assertSame($expectedPatterns, $patterns);
    }

    public function testResolveArgumentPatternsResolvesPregMatchedPatterns(): void
    {
        $this->pathParameter
            ->setScalarTypeId(1)
            ->setPattern('^123$');
        $expectedPatterns = [$this->pathParameter->getName() => '123'];
        $pathParameters   = new ObjectType([$this->pathParameter]);
        $argumentResolver = new ArgumentResolver($this->typeResolver);
        $patterns         = $argumentResolver->resolveArgumentPatterns($pathParameters);
        Assert::assertSame($expectedPatterns, $patterns);
    }

    public function testResolveArgumentPatternsResolvesPattern(): void
    {
        $scalarTypeId = 1;
        $this->pathParameter
            ->setScalarTypeId($scalarTypeId)
            ->setPattern('test_pattern');
        $expectedPatterns = [
            $this->pathParameter->getName() => $this->typeResolver->getPattern($scalarTypeId),
        ];
        $pathParameters   = new ObjectType([$this->pathParameter]);
        $argumentResolver = new ArgumentResolver($this->typeResolver);
        $patterns         = $argumentResolver->resolveArgumentPatterns($pathParameters);
        Assert::assertSame($expectedPatterns, $patterns);
    }

    public function testResolveArgumentPatternsReturnsSeveralPatterns(): void
    {
        $scalarTypeId = 1;
        $this->pathParameter
            ->setScalarTypeId($scalarTypeId)
            ->setPattern('test_pattern');

        $pathParameter2 = new Property('test2');
        $pathParameter2
            ->setScalarTypeId($scalarTypeId)
            ->setPattern('test_pattern');
        $expectedPatterns = [
            $this->pathParameter->getName() => $this->typeResolver->getPattern($scalarTypeId),
            $pathParameter2->getName() => $this->typeResolver->getPattern($scalarTypeId),
        ];
        $pathParameters   = new ObjectType([$this->pathParameter, $pathParameter2]);
        $argumentResolver = new ArgumentResolver($this->typeResolver);
        $patterns         = $argumentResolver->resolveArgumentPatterns($pathParameters);
        Assert::assertSame($expectedPatterns, $patterns);
    }
}
