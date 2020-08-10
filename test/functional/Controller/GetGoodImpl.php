<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Controller;

use OnMoon\OpenApiServerBundle\Test\Functional\Generated\Apis\PetStore\GetGood\Dto\Request\GetGoodRequestDto;
use OnMoon\OpenApiServerBundle\Test\Functional\Generated\Apis\PetStore\GetGood\Dto\Response\OK\GetGoodOKDto;
use OnMoon\OpenApiServerBundle\Test\Functional\Generated\Apis\PetStore\GetGood\GetGood;

class GetGoodImpl implements GetGood
{
    public function getGood(GetGoodRequestDto $request): GetGoodOKDto
    {
        return new GetGoodOKDto('test');
    }
}
