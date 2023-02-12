<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

use Ergebnis\Classy\Constructs;
use Illuminate\Support\Collection;
use ReflectionClass;

final class Crawler
{
    private function __construct(
        private array $names
    ) {
        $this->names = array_values($this->names);
    }

    public static function in(array $directories): Crawler
    {
        $names = (new Collection($directories))
            ->map(fn(string $directory) => Constructs::fromDirectory($directory))
            ->flatten()
            ->filter(function ($construct): bool {
                $r = new ReflectionClass($construct->name());
                
                return $r->isInterface() || (!$r->isAbstract() && !$r->isTrait());
            })
            ->map(fn($construct) => $construct->name());

        return new Crawler($names->toArray());
    }

    public function filter(callable $callback): Crawler
    {
        $filtered = array_filter($this->names, $callback);

        return new Crawler($filtered);
    }

    public function classNames(): array
    {
        return $this->names;
    }
}
