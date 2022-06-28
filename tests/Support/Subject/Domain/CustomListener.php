<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Domain;

use JeroenG\Autowire\Tests\Support\Attributes\CustomListen;

#[CustomListen(MarsClass::class)]
#[CustomListen(MoonClass::class)]
class CustomListener
{

}