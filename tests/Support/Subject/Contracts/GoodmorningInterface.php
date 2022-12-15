<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Contracts;

use JeroenG\Autowire\Tests\Support\Attributes\CustomAutotag;

#[CustomAutotag]
interface GoodmorningInterface
{
    public function goodmorning(): string;
}
