<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Autowire implements AutowireInterface
{
}
