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

Then add 
```php
OnMoon\OpenApiServerBundle\OpenApiServerBundle::class => ['all' => true],
```
to array in `config/bundles.php`.

## Usage

You can configure the bundle by adding the following parameters to your `/config/packages/open_api_server.yaml`

```yaml
open_api_server:
  # root_name_space: App\Generated # NameSpace for DTOs and Api Interfaces
  ## We will try to derive path for generated files from namespace. If you do not want them to be 
  ## stored in App namespace or if you App namespace is not in %kernel.project_dir%/src/, then you
  ## can specify path manually:
  # root_path: %kernel.project_dir%/src/Generated 
  # language_level: 7.4.0 # minimum PHP version the generated code should be compatible with
  # generated_dir_permissions: 0755 # permissions for the generated directories
  specs:
    petstore:
      path: '../spec/petstore.yaml' # path to OpenApi specification
      # type: yaml  # Specification format, either yaml or json. Extension is used if omitted
      name_space: PetStore # NameSpace for generated DTOs and Interfaces
      media_type: 'application/json' # media type from the specification files to use for generating request and response DTOs
```

Add your OpenApi specifications to the application routes configuration file using standard `resource` keyword 
with `open_api` type:

```yaml
petstore-api:
  resource: 'petstore' # This should be same as in specs section of bundle config
  type: open_api
  # prefix: '/api' # Add this standard parameter to add base path to all paths in api
  # name_prefix: 'petstore_' # This will add prefix to route names 
```

After configuring the bundle you can generate the API server code and implement the generated service interfaces 
with code that should handle the api calls.

## Commands

- Generate the server code: `php bin/console open-api:generate`
- Refresh the server code: `php bin/console open-api:refresh`
- Delete the server code: `php bin/console open-api:delete`

## Limitations:

- `number` without `format` is treated as float
- Only scalar types are allowed in path parameters
- Partial match pattern are ignored in path parameter patterns, only `^...$` patterns are used
- If pattern is specified in path parameter then type- and format-generated requirements are ignored
- Only one media-type can be used for request and response body schemas. See: https://swagger.io/docs/specification/media-types/
