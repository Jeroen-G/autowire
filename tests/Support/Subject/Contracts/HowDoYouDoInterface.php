<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Contracts;

use JeroenG\Autowire\Tests\Support\Attributes\CustomAutowire;

#[CustomAutowire]
interface HowDoYouDoInterface
{
    public function howDoYouDo(): string;
}