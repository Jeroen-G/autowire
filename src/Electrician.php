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
            throw new FaultyWiringException('oops');
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
                $configurations[] = new ConfigurationValue($need, $give, ConfigurationType::SERVICE);
            }
        }

        return new Configuration($implementation, $configurations);
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

    public function canConfigure(string $name): bool
    {
        $reflectionClass = new \ReflectionClass($name);
        $attributes = $reflectionClass->getAttributes(ConfigureAttribute::class);

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
