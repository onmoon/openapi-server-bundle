# Symfony OpenApi Server Bundle

[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fonmoon%2Fopenapi-server-bundle%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/onmoon/openapi-server-bundle/master)
[![Test Coverage](https://coveralls.io/repos/github/onmoon/openapi-server-bundle/badge.svg?branch=master)](https://coveralls.io/github/onmoon/openapi-server-bundle?branch=master)
[![Type Coverage](https://shepherd.dev/github/onmoon/openapi-server-bundle/coverage.svg)](https://shepherd.dev/github/onmoon/openapi-server-bundle)
[![Latest Stable Version](https://poser.pugx.org/onmoon/openapi-server-bundle/v/stable)](https://packagist.org/packages/onmoon/openapi-server-bundle)
[![License](https://poser.pugx.org/onmoon/openapi-server-bundle/license)](https://packagist.org/packages/onmoon/openapi-server-bundle)

## About

This bundle can generate most of the usual boilerplate code you write when implementing an API.
The code is generated from OpenAPI specifications.

The following concerns are handled by the bundle automatically:
- Route generation and routing
- Validation of incoming requests against the specification
- Strictly-typed request and response objects and API call handlers interfaces
- Calling your code containing the API call handling logic passing the request object
- Serializing of the returned response object

All you have to do is to implement the API call handler interfaces and return the provided response object.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Run

```
composer require onmoon/openapi-server-bundle 
```

Then add the bundle class to your `config/bundles.php`:
```php
<?php

return [
    OnMoon\OpenApiServerBundle\OpenApiServerBundle::class => ['all' => true],
];
```

## Usage

You can configure the bundle by adding the following parameters to your `/config/packages/open_api_server.yaml`

```yaml
open_api_server:
  #root_name_space: App\Generated # Namespace for DTOs and Api Interfaces
  ## The bundle will try to derive the paths for the generated files from the namespace. If you do not want them to be 
  ## stored in \App namespace or if you \App namespace is not in %kernel.project_dir%/src/, then you
  ## can specify this path manually:
  #root_path: %kernel.project_dir%/src/Generated 
  #language_level: 8.0.0 # minimum PHP version the generated code should be compatible with
  #generated_dir_permissions: 0755 # permissions for the generated directories
  #full_doc_blocks: false # whether to generate DocBlocks for typed variables and params  
  #send_nulls: false # return null values in responses if property is nullable and not required
  #skip_http_codes: [] # List of response codes ignored while parsing specification. 
  ## Can be any open api response code ( like 500, "5XX", "default"), or
  ## "5**", which will include both numeric (500) and XX ("5XX") codes.
  ## Might be useful if you want to generate error responses in event listener.
  specs:
    petstore:
      path: '../spec/petstore.yaml' # path to OpenApi specification
      type: yaml  # Specification format, either yaml or json. If omitted, the specification file extension will be used.
      name_space: PetStore # Namespace for generated DTOs and Interfaces
      media_type: 'application/json' # media type from the specification files to use for generating request and response DTOs
      #date_time_class: '\Carbon\CarbonImmutable' # FQCN which implements \DateTimeInterface.
      ## If set up, then generated DTOs will return instances of this class in DateTime parameters
```

Add your OpenApi specifications to the application routes configuration file using standard `resource` keyword 
with `open_api` type:

```yaml
petstore-api:
  resource: 'petstore' # This should be same as in specs section of /config/packages/open_api_server.yaml
  type: open_api
  prefix: '/api' # Add this standard parameter to add base path to all paths in api
  name_prefix: 'petstore_' # This will add a prefix to route names 
```

## Requirements for your OpenAPI schemas

For the bundle to work properly with your specifications, they should be written in OpenAPI 3.0 format and each 
operation must have an unique `operationId`.

Currently, there are also the following limitations:
- `number` without `format` is treated as float
- Only scalar types are allowed in path and query parameters
- Partial match pattern are ignored in path parameter patterns when selecting route, only `^...$` patterns are used
- If pattern is specified in path parameter then type- and format-generated patterns are ignored
- Only one media-type can be used for request and response body schemas. See: https://swagger.io/docs/specification/media-types/

## Generating the API Server code

There are two console commands that work with the generated API server code:

- Generate the server code: `php bin/console open-api:generate`
- Delete the server code: `php bin/console open-api:delete`

Most of the time you should use the `generate` command.
It will clear the bundle cache, delete the old generated server code if it exists and generate the new code.

Be careful with the generate and delete commands, they will delete the entire contents of the directory you have specified 
in `root_path` in the `/config/packages/open_api_server.yaml` file. That directory should contain no files except 
the code generated by this bundle, as it will be deleted every time you generate the API server code.

For each operation described in the specification, a API call handler interface will be generated that you should implement
to handle the API calls.

## Implementing the API call handlers interfaces

Given the following generated API handler interface:
```php
<?php

declare (strict_types=1);

namespace App\Generated\Apis\PetStore\ShowPetById;

use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use App\Generated\Apis\PetStore\ShowPetById\Dto\Request\ShowPetByIdRequestDto;
use App\Generated\Apis\PetStore\ShowPetById\Dto\Response\ShowPetByIdResponse;

/**
 * This interface was automatically generated
 * You should not change it manually as it will be overwritten
 */
interface ShowPetById extends RequestHandler
{
    /** Info for a specific pet */
    public function showPetById(ShowPetByIdRequestDto $request) : ShowPetByIdResponse;
}
```

Your API call handler could look like this:
```php
<?php

namespace App\Api;

use App\Repository\PetRepository;
use App\Generated\Apis\PetStore\ShowPetById\Dto\Request\ShowPetByIdRequestDto;
use App\Generated\Apis\PetStore\ShowPetById\Dto\Response\OK\ShowPetByIdResponseDto;
use App\Generated\Apis\PetStore\ShowPetById\Dto\Response\ShowPetByIdResponse;
use App\Generated\Apis\PetStore\ShowPetById\ShowPetById;

class ShowPetByIdHandler implements ShowPetById
{
    private PetRepository $pets;

    public function __construct(PetRepository $pets)
    {
        $this->pets = $pets;
    }

    public function showPetById(ShowPetByIdRequestDto $request) : ShowPetByIdResponse
    {
        $petId = $request->getPathParameters()->getPetId();
        $pet   = $this->pets->getById($petId);

        return new ShowPetByIdResponseDto($pet->id(), $pet->name());
    }
}
```

Additionally, your API call handler can implement the following interfaces:
- `\OnMoon\OpenApiServerBundle\Interfaces\SetClientIp` - if it needs the client IP address
- `\OnMoon\OpenApiServerBundle\Interfaces\SetRequest` - if it needs the Symfony request object
- `\OnMoon\OpenApiServerBundle\Interfaces\GetResponseCode` - if it needs to specify custom HTTP response codes

## Using DTO mapper

If you want to use Doctrine entities or other business logic classes as sources for API 
response, you can easily copy contents into DTO using DTO mapper.

Install it with 
```
composer require onmoon/dto-mapper
```

And use like follows
```php
public function showPetById(ShowPetByIdRequestDto $request) : ShowPetByIdResponseDto
{
    $petId = $request->getPathParameters()->getPetId();
    $pet   = $this->pets->getById($petId);

    /** @var OnMoon\DtoMapper\DtoMapper $this->mapper */
    return $this->mapper->map($pet, ShowPetByIdResponseDto::class);
}
```

[More information](https://github.com/onmoon/dto-mapper)

## Customizing the API server behavior

During the request handling lyfecycle the API server emits several events that can be used instead
of the built-in Symfony Kernel events as the former provide more context. Theese events allow
hooking into the API server functionality and modify it's behavior.

The following events are available:

- `OnMoon\OpenApiServerBundle\Event\Server\RequestEvent`

    The RequestEvent event occurs right before the request is validated against the OpenAPI Schema.
    This event allows you to modify the Operation and Request objects prior to performing the 
    validation and processing the request.
- `OnMoon\OpenApiServerBundle\Event\Server\RequestDtoEvent`

    The RequestDtoEvent event occurs after the Request contents are deserialized in a Dto object representing
    the API request and before this object is passed to your RequestHandler implementation.
    This event allows you to modify the Operation and Request DTO (only via reflection) before it will be passed to your 
    RequestHandler implementation.
    Note that the ResponseDTO is not created if the API endpoint expects no request body, path or query parameters.
- `OnMoon\OpenApiServerBundle\Event\Server\ResponseDtoEvent`

    The ResponseDtoEvent event occurs after the request handler class was executed returning a ResponseDto and
    before this ResponseDto is serialized to a Response.
    This event allows you to modify the ResponseDto contents before it will be serialized. This can be used as an
    alternative to modyfing the Response object in a Symfony ResponseEvent, avoiding unnecessary decoding/encoding
    of the Response body json.
    Note that the ResponseDTO is not created if the API endpoint has no response body.
- `OnMoon\OpenApiServerBundle\Event\Server\ResponseEvent`

    The ResponseEvent event occurs right before the response is sent by the API server.
    This event allows you to modify the Response object before the server will emit it to the client.

## Customizing API server code generation

During API server code generation the code generator emits several events that can be used to
modify the generated code either by changing parts of the OpenAPI specification objects or
by changing the objects representing the various code definitions like classes, properties, methods.

The following events are available:

- `OnMoon\OpenApiServerBundle\Event\CodeGenerator\ClassGraphReadyEvent`

    The ClassGraphReadyEvent event occurs after all specifications
    has been parsed and graph of classes to be generated has been
    constructed.
    
    This event allows you to modify:
    * Class names, namespaces and paths,
    * Property attributes, getters and setters,
    * Base interfaces and classes.

- `OnMoon\OpenApiServerBundle\Event\CodeGenerator\FilesReadyEvent`

    The FilesReadyEvent event occurs after all class files
    are generated before they are written to files.
    
    This event allows you to modify generated files content,
    e.g. change code style.
