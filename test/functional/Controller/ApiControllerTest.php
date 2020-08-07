<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Webmozart\Assert\Assert;

class ApiControllerTest extends WebTestCase
{
    public function testTest(): void
    {
        Assert::true(true);
    }
}
