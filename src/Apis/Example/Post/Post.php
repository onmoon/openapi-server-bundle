<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Apis\Example\Post;

use OnMoon\OpenApiServerBundle\Apis\Example\Post\Dto\Request\PostRequestDto;
use OnMoon\OpenApiServerBundle\Apis\Example\Post\Dto\Response\OK\PostOKDto;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
/**
 * This interface was automatically generated
 * You should not change it manually as it will be overwritten
 */

interface Post extends RequestHandler
{
    public function post(PostRequestDto $request): PostOKDto;
}
