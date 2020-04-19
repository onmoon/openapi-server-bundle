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
        $fields = [
            'body' => $bodyDtoDefinition,
            'queryParameters' => $queryParameters,
            'pathParameters' => $pathParameters
        ];

        $properties = [];

        foreach ($fields as $name => $definition) {
            if($definition !== null) {
                $properties[] = (new PropertyDefinition($name))
                    ->setObjectTypeDefinition($definition)
                    ->setRequired(true)
                    ->setNullable(false);
            }
        }

        parent::__construct($properties);
    }
}
