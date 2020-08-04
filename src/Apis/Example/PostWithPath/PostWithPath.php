<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Apis\Example\PostWithPath;

use OnMoon\OpenApiServerBundle\Apis\Example\PostWithPath\Dto\Request\PostWithPathRequestDto;
use OnMoon\OpenApiServerBundle\Apis\Example\PostWithPath\Dto\Response\OK\PostWithPathOKDto;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
/**
 * This interface was automatically generated
 * You should not change it manually as it will be overwritten
 */

interface PostWithPath extends RequestHandler
{
    public function postWithPath(PostWithPathRequestDto $request): PostWithPathOKDto;
}
