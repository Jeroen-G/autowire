<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Domain;

use JeroenG\Autowire\Tests\Support\Subject\Contracts\HelloInterface;

class WorldClass implements HelloInterface
{
    public function hello(): string
    {
        return 'world';
    }
}
