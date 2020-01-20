# Symfony OpenApi Server Bundle

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require onmoon/openapi-server-bundle
```

or add

```
"onmoon/openapi-server-bundle": "^0.0"
```

to the require section of your `composer.json` file.

## Confirugation

You can configure the bundle by adding the following parameters to your `/config/services.xml`
```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services
        http://symfony.com/schema/dic/services/services-1.0.xsd">
    <parameters>
        <parameter key="openapi.generated.code.language.level">7.4.0</parameter>
        <parameter key="openapi.generated.code.dir.permissions">0755</parameter>
        <parameter key="openapi.generated.code.root.namespace">App\Generated</parameter>
        <parameter key="openapi.generated.code.root.path">%kernel.project_dir%/src/Generated</parameter>
        <parameter key="openapi.generated.code.media.type">application/json</parameter>
    </parameters>
</container>
```
`openapi.generated.code.language.level` - minimum PHP version the generated code should be compatible with

`openapi.generated.code.dir.permissions` - permissions for the generated directories

`openapi.generated.code.root.namespace` - root namespace for the generated code

`openapi.generated.code.root.path` - absolute path to the directory where the code will be generated

`openapi.generated.code.media.type` - media type from the specification files to use for generating request and response DTOs

## Usage

Add your OpenApi specifications to the application routes configuration file:

```yaml
first-api:
  resource: '../spec/first.yaml'
  type: openapi-yaml
  prefix: '/first'
  name_prefix: 'first_'

second-api:
  resource: '../spec/second.yaml'
  type: openapi-yaml
  prefix: '/second'
  name_prefix: 'second_'
```

Generate the server code: `php bin/console app:openapi-generate-code`

Now you can implement the service interfaces generated by the previous command and put the code that should
handle the api calls there.

## Limitations:

- `number` without `format` is treated as float
- Only scalar types are allowed in path parameters
- Partial match pattern are ignored in path parameter patterns, only `^...$` patterns are used
- If pattern is specified in path parameter then type- and format-generated requirements are ignored
- Only one media-type can be used for request and response body schemas. See: https://swagger.io/docs/specification/media-types/