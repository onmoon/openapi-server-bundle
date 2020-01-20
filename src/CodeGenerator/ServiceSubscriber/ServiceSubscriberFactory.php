<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\ServiceSubscriber;

use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;

interface ServiceSubscriberFactory
{
    /**
     * @param GeneratedClass[] $serviceInterfaces
     * @return GeneratedClass
     */
    public function generateServiceSubscriber(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        array $serviceInterfaces
    ) : GeneratedClass;
}
