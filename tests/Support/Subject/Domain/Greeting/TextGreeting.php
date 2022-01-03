<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Domain\Greeting;

use JeroenG\Autowire\Attribute\Configure;

#[Configure(['$greeting' => 'Good day to you!'])]
class TextGreeting
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
