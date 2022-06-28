<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

use Attribute;
use JeroenG\Autowire\Attribute\Autowire as AutowireAttribute;
use JeroenG\Autowire\Attribute\AutowireInterface as AutowireAttributeInterface;
use JeroenG\Autowire\Attribute\Listen;
use JeroenG\Autowire\Attribute\Listen as ListenAttribute;
use JeroenG\Autowire\Attribute\Configure as ConfigureAttribute;
use JeroenG\Autowire\Attribute\ConfigureInterface as ConfigureAttributeInterface;
use JeroenG\Autowire\Attribute\ListenInterface as ListenAttributeInterface;
use JeroenG\Autowire\Exception\FaultyWiringException;
use JeroenG\Autowire\Exception\InvalidAttributeException;
use ReflectionAttribute;
use ReflectionClass;

final class Electrician
{
    /**
     * @param class-string $autowireAttribute
     * @param class-string $listenAttribute
     * @param class-string $configureAttribute
     */
    public function __construct(
        private Crawler $crawler,
        private string $autowireAttribute = AutowireAttribute::class,
        private string $configureAttribute = ConfigureAttribute::class,
        private string $listenAttribute = ListenAttribute::class,
    )
    {
        self::checkValidAttributeImplementation($this->autowireAttribute, AutowireAttributeInterface::class);
        self::checkValidAttributeImplementation($this->configureAttribute, ConfigureAttributeInterface::class);
        self::checkValidAttributeImplementation($this->listenAttribute, ListenAttributeInterface::class);
    }

    public function connect(string $interface): Wire
    {
        $implementation = $this->findImplementation($interface);

        return new Wire($interface, $implementation);
    }

    public function configure(string $implementation): Configuration
    {
        $reflectionClass = new ReflectionClass($implementation);
        $attributes = $reflectionClass->getAttributes($this->configureAttribute);

        if (empty($attributes)) {
            throw FaultyWiringException::classHasNoAttribute($implementation, $this->configureAttribute);
        }

        $configurations = [];

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

    public function events(string $implementation): array
    {
        $reflectionClass = new ReflectionClass($implementation);
        $attributes = $reflectionClass->getAttributes($this->listenAttribute);

        if (empty($attributes)) {
            throw FaultyWiringException::classHasNoAttribute($implementation, $this->listenAttribute);
        }

        $events = [];

        foreach ($attributes as $attribute) {
            /** @var ListenAttribute $instance */
            $instance = $attribute->newInstance();

            $events[] = $instance->event;
        }

        return $events;
    }

    public function canAutowire(string $name): bool
    {
        return $this->classHasAttribute($name, $this->autowireAttribute);
    }

    public function canListen(string $name): bool
    {
        return $this->classHasAttribute($name, $this->listenAttribute);
    }

    public function canConfigure(string $name): bool
    {
        return $this->classHasAttribute($name, $this->configureAttribute);
    }

    /**
     * @throws InvalidAttributeException
     */
    private static function checkValidAttributeImplementation(string $className, string $attributeInterface): void
    {
        if (! class_exists($className)) {
            throw InvalidAttributeException::doesNotExist($className);
        }

        if (empty((new ReflectionClass($className))->getAttributes(Attribute::class))) {
            throw InvalidAttributeException::isNotAnAttribute($className);
        }

        if (! is_a($className, $attributeInterface, true)) {
            throw InvalidAttributeException::doesNotImplementInterface($className, $attributeInterface);
        }
    }

    private function classHasAttribute(string $className, string $attributeName): bool
    {
        $reflectionClass = new ReflectionClass($className);
        $attributes = $reflectionClass->getAttributes($attributeName, ReflectionAttribute::IS_INSTANCEOF);

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
