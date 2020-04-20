<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class RequestDtoDefinition extends DtoDefinition
{
    public function __construct(
        ?RequestBodyDtoDefinition $bodyDtoDefinition,
        ?RequestParametersDtoDefinition $queryParameters,
        ?RequestParametersDtoDefinition $pathParameters
    )
    {
        $fields = [
            'pathParameters' => $pathParameters,
            'queryParameters' => $queryParameters,
            'body' => $bodyDtoDefinition,
        ];

        $properties = [];

        foreach ($fields as $name => $definition) {
            if ($definition !== null) {
                $properties[] = (new PropertyDefinition($name))
                    ->setObjectTypeDefinition($definition)
                    ->setRequired(true)
                    ->setNullable(false);
            }
        }

        parent::__construct($properties);
    }
}
