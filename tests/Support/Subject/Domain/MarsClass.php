<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Domain;

class MarsClass
{
    public function hello(): string
    {
        return 'mars';
    }
}
