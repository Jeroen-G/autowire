<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

use Ergebnis\Classy\Constructs;
use JeroenG\Autowire\Attribute\Autowire as AutowireAttribute;

final class Electrician
{
    public function __construct(
        private Crawler $crawler
    ) {
    }

    public function connect(string $interface): Wire
    {
        $implementation = $this->findImplementation($interface);

        return new Wire($interface, $implementation);
    }

    public function canAutowire(string $name): bool
    {
        $reflectionClass = new \ReflectionClass($name);
        $attributes = $reflectionClass->getAttributes(AutowireAttribute::class);

        if (empty($attributes)) {
            return false;
        }

        return true;
    }

    private function findImplementation(string $interface): string
    {
        foreach ($this->crawler->classNames() as $className) {
            if (is_subclass_of($className, $interface)) {
                return $className;
            }
        }

        throw new \Exception("No implementation found for $interface");
    }
}
