<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Attribute;

use Attribute;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS)]
final class Autotag implements AutotagInterface
{
    public function __construct(
        private string $tag = '',
    ) {
    }
    
    public function getTag(ReflectionClass $targetInterface): string
    {
        return $this->tag ?: $targetInterface->getName();
    }
}
