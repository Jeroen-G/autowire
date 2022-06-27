<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Attributes;

use Attribute;
use JeroenG\Autowire\Attribute\ListenInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class CustomListen implements ListenInterface
{
    public function __construct(public string $event)
    {
    }
}
