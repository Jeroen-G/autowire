<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

use JeroenG\Autowire\Attribute\Autowire as AutowireAttribute;
use JeroenG\Autowire\Attribute\AutowireInterface as AutowireAttributeInterface;
use JeroenG\Autowire\Attribute\Configure as ConfigureAttribute;
use JeroenG\Autowire\Attribute\ConfigureInterface as ConfigureAttributeInterface;
use JeroenG\Autowire\Exception\FaultyWiringException;
use Webmozart\Assert\Assert;

final class Electrician
{
    /**
     * @param class-string $autowireAttribute
     * @param class-string $configureAttribute
     */
    public function __construct(
        private Crawler $crawler,
        private string $autowireAttribute = AutowireAttribute::class,
        private string $configureAttribute = ConfigureAttribute::class
    ) {        
        self::assertValidAttributeImplementation($this->autowireAttribute, AutowireAttributeInterface::class);
        self::assertValidAttributeImplementation($this->configureAttribute, ConfigureAttributeInterface::class);
    }

    public function connect(string $interface): Wire
    {
        $implementation = $this->findImplementation($interface);

        return new Wire($interface, $implementation);
    }

    public function configure(string $implementation): Configuration
    {
        $reflectionClass = new \ReflectionClass($implementation);
        $attributes = $reflectionClass->getAttributes($this->configureAttribute);
        $configurations = [];

        if (empty($attributes)) {
            throw FaultyWiringException::classHasNoAttribute($implementation, $this->configureAttribute);
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
        return $this->classHasAttribute($name, $this->autowireAttribute);
    }

    public function canConfigure(string $name): bool
    {
        return $this->classHasAttribute($name, $this->configureAttribute);
    }
    
    private static function assertValidAttributeImplementation(string $className, string $attributeInterface): void
    {
        Assert::classExists($className);
        Assert::notEmpty((new \ReflectionClass($className))->getAttributes(\Attribute::class));
        Assert::isAOf($className, $attributeInterface, "{$className} : {$attributeInterface}");
    }

    private function classHasAttribute(string $className, string $attributeName): bool
    {
        $reflectionClass = new \ReflectionClass($className);
        $attributes = $reflectionClass->getAttributes($attributeName, \ReflectionAttribute::IS_INSTANCEOF);        

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
