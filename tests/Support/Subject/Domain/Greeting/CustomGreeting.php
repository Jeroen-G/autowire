<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Domain\Greeting;

use JeroenG\Autowire\Tests\Support\Attributes\CustomConfigure;

#[CustomConfigure(['$greeting' => 'Good day to you!'])]
class CustomGreeting
{
    private $greeting;

    public function __construct($greeting)
    {
        $this->greeting = $greeting;
    }

    public function getGreeting()
    {
        return $this->greeting;
    }
}
