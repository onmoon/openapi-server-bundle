<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use OnMoon\OpenApiServerBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTestCase extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }
}
