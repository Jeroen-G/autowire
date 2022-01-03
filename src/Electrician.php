<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

use JeroenG\Autowire\Attribute\Autowire as AutowireAttribute;
use JeroenG\Autowire\Attribute\Configure as ConfigureAttribute;
use JeroenG\Autowire\Exception\FaultyWiringException;

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

    public function configure(string $implementation): Configuration
    {
        $reflectionClass = new \ReflectionClass($implementation);
        $attributes = $reflectionClass->getAttributes(ConfigureAttribute::class);
        $configurations = [];

        if (empty($attributes)) {
            throw FaultyWiringException::classHasNoAttribute($implementation, ConfigureAttribute::class);
        }

        foreach ($attributes as $attribute) {
            /** @var ConfigureAttribute $instance */
            $instance = $attribute->newInstance();

            foreach ($instance->getConfigs() as $need => $give) {
                $configurations[] = new ConfigurationValue($need, $give, ConfigurationType::CONFIG);
            }

            foreach ($instance->getServices() as $need => $give) {
                $configurations[] = new ConfigurationValue($need, $give, ConfigurationType::SERVICE);
            }

            foreach ($instance->getDefinitions() as $need => $give) {
                $configurations[] = new ConfigurationValue($need, $give, ConfigurationType::UNKNOWN);
            }
        }

        return new Configuration($implementation, $configurations);
    }

    public function canAutowire(string $name): bool
    {
        return $this->classHasAttribute($name, AutowireAttribute::class);
    }

    public function canConfigure(string $name): bool
    {
        return $this->classHasAttribute($name, ConfigureAttribute::class);
    }

    private function classHasAttribute(string $className, string $attributeName): bool
    {
        $reflectionClass = new \ReflectionClass($className);
        $attributes = $reflectionClass->getAttributes($attributeName);

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

        throw FaultyWiringException::implementationNotFoundFor($interface);
    }
}
