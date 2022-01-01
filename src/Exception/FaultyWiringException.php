<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Exception;

final class FaultyWiringException extends \RuntimeException
{
    public static function implementationNotFoundFor(string $interface): self
    {
        return new self("No implementation found for $interface");
    }
}
