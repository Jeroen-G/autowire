<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Attributes;

use Attribute;
use JeroenG\Autowire\Attribute\AutowireInterface;
use JeroenG\Autowire\Attribute\ListenInterface;

class NotAnAttribute implements AutowireInterface
{
    public function __construct(public string $nope)
    {
    }
}
