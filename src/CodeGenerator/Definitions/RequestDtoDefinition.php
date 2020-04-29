<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;

class RequestDtoDefinition extends DtoDefinition
{
    public function __construct(
        ?RequestBodyDtoDefinition $bodyDtoDefinition,
        ?RequestParametersDtoDefinition $queryParameters,
        ?RequestParametersDtoDefinition $pathParameters
    ) {
        $fields = [
            'pathParameters' => $pathParameters,
            'queryParameters' => $queryParameters,
            'body' => $bodyDtoDefinition,
        ];

        $properties = [];

        foreach ($fields as $name => $definition) {
            if ($definition === null) {
                continue;
            }

            $specProperty = (new Property($name))->setRequired(true);
            $properties[] = (new PropertyDefinition($specProperty))
                ->setObjectTypeDefinition($definition);
        }

        parent::__construct($properties);
    }
}
