<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Attributes;

use Attribute;
use JeroenG\Autowire\Attribute\TagInterface;
use ReflectionClass;

#[Attribute(Attribute::TARGET_CLASS)]
class CustomTag implements TagInterface
{
    public function getTag(ReflectionClass $targetInterface): string
    {
        return $targetInterface->getName();
    }
}
