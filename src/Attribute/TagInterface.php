<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Attribute;

use ReflectionClass;

interface TagInterface
{
    public function getTag(ReflectionClass $targetInterface): string;
}
