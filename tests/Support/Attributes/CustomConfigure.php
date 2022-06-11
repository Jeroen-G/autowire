<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Attributes;

use Attribute;
use JeroenG\Autowire\Attribute\ConfigureInterface;

#[Attribute(Attribute::TARGET_CLASS)]
class CustomConfigure implements ConfigureInterface
{
    private array $configs = [];

    private array $services = [];

    private array $definitions = [];

    public function __construct(array $cables)
    {
        foreach ($cables as $variable => $value) {
            $this->parse($variable, $value);
        }
    }

    public function getConfigs(): array
    {
        return $this->configs;
    }

    public function getServices(): array
    {
        return $this->services;
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    private function parse(string $variable, string $value): void
    {
        $hasConfig = preg_match("/%(.*)%/", $value, $config);

        if ($hasConfig === 1) {
            $this->configs[$variable] = $config[1];
            return;
        }

        $hasService = preg_match("/@(.*)/", $value, $service);

        if ($hasService === 1) {
            $this->services[$variable] = $service[1];
            return;
        }

        $this->definitions[$variable] = $value;
    }
}
