<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

class RequestDtoDefinition extends DtoDefinition
{
    public function __construct(
        ?RequestBodyDtoDefinition $bodyDtoDefinition,
        ?RequestParametersDtoDefinition $queryParameters,
        ?RequestParametersDtoDefinition $pathParameters
    ) {
        $properties = [];
        if($bodyDtoDefinition !== null) {
            $properties[] = (new PropertyDefinition('body'))->setObjectTypeDefinition($bodyDtoDefinition);
        }
        if($queryParameters !== null) {
            $properties[] = (new PropertyDefinition('queryParameters'))->setObjectTypeDefinition($queryParameters);
        }
        if($pathParameters !== null) {
            $properties[] = (new PropertyDefinition('pathParameters'))->setObjectTypeDefinition($pathParameters);
        }

        parent::__construct($properties);
    }
}
