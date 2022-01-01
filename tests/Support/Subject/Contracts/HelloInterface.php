<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Contracts;

use JeroenG\Autowire\Attribute\Autowire;

#[Autowire]
interface HelloInterface
{
    public function hello(): string;
}
