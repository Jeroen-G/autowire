<?php declare(strict_types=1);

namespace JeroenG\Autowire\Testing;

class CachedState
{
    /**
     * This is used to store the cached autowire configuration when running tests using the `WithCachedAutowire` trait
     */
    public static ?array $cachedAutowire = null;
}