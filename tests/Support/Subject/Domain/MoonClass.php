<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Domain;

use JeroenG\Autowire\Tests\Support\Subject\Contracts\HowDoYouDoInterface;

class MoonClass implements HowDoYouDoInterface
{
    public function howDoYouDo(): string
    {
        return 'how do you do?';
    }
}
