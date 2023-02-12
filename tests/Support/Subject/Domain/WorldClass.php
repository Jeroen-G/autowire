<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Domain;

use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodafternoonInterface;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodeveningInterface;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\HelloInterface;

class WorldClass implements 
    GoodafternoonInterface, 
    GoodeveningInterface, 
    HelloInterface
{
    public function goodafternoon(): string
    {
        return 'good afternoon';
    }
    
    public function goodevening(): string
    {
        return 'good evening';
    }
    
    public function hello(): string
    {
        return 'world';
    }
}
