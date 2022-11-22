<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;

use function array_map;
use function count;
use function implode;
use function Safe\sprintf;

class InterfaceCodeGenerator extends CodeGenerator
{
    public function generate(RequestHandlerInterfaceDefinition $definition): GeneratedFileDefinition
    {
        $fileBuilder = new FileBuilder($definition);

        $interfaceBuilder = $this
            ->factory
            ->interface($fileBuilder->getReference($definition))
            ->setDocComment(sprintf(self::AUTOGENERATED_WARNING, 'interface'));

        $extends = $definition->getExtends();
        if ($extends !== null) {
            $interfaceBuilder->extend($fileBuilder->getReference($extends));
        }

        $methodBuilder = $this->factory->method($definition->getMethodName())->makePublic();
        $request       = $definition->getRequestType();
        $docBlocks     = [];

        if ($request !== null) {
            $requestClass = $fileBuilder->getReference($request);
            $methodBuilder->addParam(
                $this->factory->param('request')->setType($requestClass)
            );
            if ($this->fullDocs) {
                $docBlocks[] = sprintf(
                    '@param %s $%s',
                    $requestClass,
                    'request'
                );
            }
        }

        $responses = $definition->getResponseTypes();
        if (count($responses)) {
            $responseClasses = array_map(static fn (ClassDefinition $response) => $fileBuilder->getReference($response), $responses);
            $unionClass      = implode('|', $responseClasses);
            $methodBuilder->setReturnType($unionClass);
            if ($this->fullDocs) {
                $docBlocks[] = sprintf(
                    '@return %s',
                    $unionClass
                );
            }
        } else {
            $methodBuilder->setReturnType('void');
        }

        $description = $definition->getMethodDescription();
        if ($description !== null) {
            if (count($docBlocks) > 0) {
                $docBlocks = [$description, '', ...$docBlocks];
            } else {
                $docBlocks[] = $description;
            }
        }

        if (count($docBlocks) > 0) {
            $methodBuilder->setDocComment($this->getDocComment($docBlocks));
        }

        $interfaceBuilder->addStmt($methodBuilder);

        $fileBuilder = $fileBuilder->addStmt($interfaceBuilder);

        return new GeneratedFileDefinition(
            $definition,
            $this->printFile($fileBuilder)
        );
    }
}
