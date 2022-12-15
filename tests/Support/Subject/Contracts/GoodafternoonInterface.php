<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Contracts;

use JeroenG\Autowire\Attribute\Autotag;

#[Autotag]
interface GoodafternoonInterface
{
    public function goodafternoon(): string;
}
