<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Attribute;

use Attribute;
use JeroenG\Autowire\Exception\InvalidAttributeException;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class Listen implements ListenInterface
{
    public function __construct(public string $event) {
    }
}
