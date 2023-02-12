<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Contracts;

use JeroenG\Autowire\Attribute\Tag;

#[Tag]
interface GoodafternoonInterface
{
    public function goodafternoon(): string;
}
