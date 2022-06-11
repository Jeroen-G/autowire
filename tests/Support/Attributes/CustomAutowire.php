<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Attributes;

use Attribute;
use JeroenG\Autowire\Attribute\AutowireInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class CustomAutowire implements AutowireInterface
{
}
