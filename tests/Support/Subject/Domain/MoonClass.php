<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Domain;

use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodafternoonInterface;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodmorningInterface;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\HowDoYouDoInterface;

class MoonClass implements
    GoodafternoonInterface, 
    GoodmorningInterface, 
    HowDoYouDoInterface
{
    public function goodafternoon(): string
    {
        return 'good afternoon';
    }
    
    public function goodmorning(): string
    {
        return 'good morning';
    }
    
    public function howDoYouDo(): string
    {
        return 'how do you do?';
    }
}
