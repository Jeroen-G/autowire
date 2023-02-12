<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Domain;

abstract class AbstractClass
{        
    public function howdy(): string
    {
        return 'howdy';
    }
}
