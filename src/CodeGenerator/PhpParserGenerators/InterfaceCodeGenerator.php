<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use function count;
use function Safe\sprintf;

class InterfaceCodeGenerator extends CodeGenerator
{
    public function generate(GeneratedInterfaceDefinition $definition) : GeneratedFileDefinition
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

        if ($definition instanceof RequestHandlerInterfaceDefinition) {
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

            $response = $definition->getResponseType();
            if ($response !== null) {
                $responseClass = $fileBuilder->getReference($response);
                $methodBuilder->setReturnType($responseClass);
                if ($this->fullDocs) {
                    $docBlocks[] = sprintf(
                        '@return %s',
                        $responseClass
                    );
                }
            } else {
                $methodBuilder->setReturnType('void');
            }

            $description = $definition->getMethodDescription();
            if ($description !== null) {
                if (count($docBlocks)) {
                    $docBlocks = [$description, '', ...$docBlocks];
                } else {
                    $docBlocks[] = $description;
                }
            }

            if (count($docBlocks) > 0) {
                $methodBuilder->setDocComment($this->getDocComment($docBlocks));
            }

            $interfaceBuilder->addStmt($methodBuilder);
        }

        $fileBuilder = $fileBuilder->addStmt($interfaceBuilder);

        return new GeneratedFileDefinition(
            $definition,
            $this->printFile($fileBuilder)
        );
    }
}
