<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Exception;

final class InvalidAttributeException extends \RuntimeException
{
    public static function doesNotExist(string $class): self
    {
        return new self("Class $class does not exist");
    }

    public static function doesNotImplementInterface(string $class, string $interface): self
    {
        return new self("Class $class does not implement $interface");
    }
    
    public static function isNotAnAttribute(string $class): self
    {
        return new self("Class $class is not an attribute");
    }
}
