<?php

declare(strict_types=1);

use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Lukasoppermann\Httpstatus\Httpstatus;
use Nyholm\Psr7\Factory\Psr17Factory;
use OnMoon\OpenApiServerBundle\CodeGenerator\ApiServerCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\AttributeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\FileGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FilePutContentsFileWriter;
use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FileWriter;
use OnMoon\OpenApiServerBundle\CodeGenerator\GraphGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\NameGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\DefaultNamingStrategy;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\DtoCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\InterfaceCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\ServiceSubscriberCodeGenerator;
use OnMoon\OpenApiServerBundle\Command\DeleteGeneratedCodeCommand;
use OnMoon\OpenApiServerBundle\Command\GenerateApiCodeCommand;
use OnMoon\OpenApiServerBundle\Controller\ApiController;
use OnMoon\OpenApiServerBundle\Router\RouteLoader;
use OnMoon\OpenApiServerBundle\Serializer\ArrayDtoSerializer;
use OnMoon\OpenApiServerBundle\Serializer\DtoSerializer;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Specification\SpecificationParser;
use OnMoon\OpenApiServerBundle\Types\ArgumentResolver;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use OnMoon\OpenApiServerBundle\Validator\LeaguePSR7RequestSchemaValidator;
use OnMoon\OpenApiServerBundle\Validator\RequestSchemaValidator;
use PhpParser\BuilderFactory;
use sspat\ReservedWords\ReservedWords;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator
        ->services()
        ->defaults()
        ->autoconfigure()
        ->autowire()
        ->set(Httpstatus::class)
        ->set(ReservedWords::class)
        ->set(RouteLoader::class)
            ->tag('routing.loader')
        ->set(ValidatorBuilder::class)
        ->set(Psr17Factory::class)
        ->set(PsrHttpFactory::class)
            ->args([
                new ReferenceConfigurator(Psr17Factory::class),
                new ReferenceConfigurator(Psr17Factory::class),
                new ReferenceConfigurator(Psr17Factory::class),
                new ReferenceConfigurator(Psr17Factory::class),
            ])
        ->set(RequestSchemaValidator::class, LeaguePSR7RequestSchemaValidator::class)
        ->set(ApiController::class)
            ->tag('controller.service_arguments')
        ->set(ArgumentResolver::class)
        ->set(ScalarTypesResolver::class)
        ->set(BuilderFactory::class)
        ->set(NamingStrategy::class, DefaultNamingStrategy::class)
            ->args([
                '$rootNamespace' => '%openapi.generated.code.root.namespace%',
                '$languageLevel' => '%openapi.generated.code.language.level%',
            ])
        ->set(DtoCodeGenerator::class)
            ->args([
                '$languageLevel' => '%openapi.generated.code.language.level%',
                '$fullDocs' => '%openapi.generated.code.full.doc.blocks%',
            ])
        ->set(InterfaceCodeGenerator::class)
            ->args([
                '$languageLevel' => '%openapi.generated.code.language.level%',
                '$fullDocs' => '%openapi.generated.code.full.doc.blocks%',
            ])
        ->set(ServiceSubscriberCodeGenerator::class)
            ->args([
                '$languageLevel' => '%openapi.generated.code.language.level%',
                '$fullDocs' => '%openapi.generated.code.full.doc.blocks%',
            ])
        ->set(FileWriter::class, FilePutContentsFileWriter::class)
            ->args(['%openapi.generated.code.dir.permissions%'])
        ->set(GenerateApiCodeCommand::class)
            ->args([
                '$cache' => new ReferenceConfigurator('cache.app.taggable'),
                '$rootPath' => '%openapi.generated.code.root.path%',
            ])
        ->set(ApiServerCodeGenerator::class)
        ->set(DeleteGeneratedCodeCommand::class)
            ->args(['%openapi.generated.code.root.path%'])
        ->set(DtoSerializer::class, ArrayDtoSerializer::class)
            ->args(['$sendNulls' => '%openapi.send.nulls%'])
        ->set(SpecificationLoader::class)
            ->args([
                '$locator' => new ReferenceConfigurator('file_locator'),
                '$cache' => new ReferenceConfigurator('cache.app.taggable'),
            ])
        ->set(SpecificationParser::class)
            ->args(['$skipHttpCodes' => '%openapi.generated.code.skip.http.codes%'])
        ->set(GraphGenerator::class)
        ->set(AttributeGenerator::class)
        ->set(FileGenerator::class)
        ->set(NameGenerator::class)
            ->args([
                '$rootNamespace' => '%openapi.generated.code.root.namespace%',
                '$rootPath' => '%openapi.generated.code.root.path%',
            ]);
};
