<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

use Webmozart\Assert\Assert;

final class Configuration
{
    public string $implementation;

    /** @var ConfigurationValue[] */
    public array $definitions;

    public function __construct(string $implementation, array $values)
    {
        Assert::allIsInstanceOf($values, ConfigurationValue::class);
        $this->implementation = $implementation;
        $this->definitions = $values;
    }
}
