<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Domain;

use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodeveningInterface;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodmorningInterface;

class MarsClass implements 
    GoodeveningInterface,
    GoodmorningInterface
{        
    public function goodevening(): string
    {
        return 'good evening';
    }
    
    public function goodmorning(): string
    {
        return 'good morning';
    }
    
    public function hello(): string
    {
        return 'mars';
    }
}
