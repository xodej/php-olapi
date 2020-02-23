<?php

declare(strict_types=1);

namespace Xodej\Olapi\ApiRequestParams;

interface IRequest
{
    public function url(): ?string;

    public function asArray(): ?array;
}
