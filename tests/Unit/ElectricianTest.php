<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Unit;

use Generator;
use JeroenG\Autowire\ConfigurationType;
use JeroenG\Autowire\ConfigurationValue;
use JeroenG\Autowire\Crawler;
use JeroenG\Autowire\Electrician;
use JeroenG\Autowire\Exception\FaultyWiringException;
use JeroenG\Autowire\Exception\InvalidAttributeException;
use JeroenG\Autowire\Tests\Support\Attributes\CustomAutowire;
use JeroenG\Autowire\Tests\Support\Attributes\CustomConfigure;
use JeroenG\Autowire\Tests\Support\Attributes\CustomListen;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodbyeInterface;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\HelloInterface;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\HowDoYouDoInterface;
use JeroenG\Autowire\Tests\Support\Subject\Domain\CustomListener;
use JeroenG\Autowire\Tests\Support\Subject\Domain\Greeting\ClassGreeting;
use JeroenG\Autowire\Tests\Support\Subject\Domain\Greeting\ConfigGreeting;
use JeroenG\Autowire\Tests\Support\Subject\Domain\Greeting\CustomGreeting;
use JeroenG\Autowire\Tests\Support\Subject\Domain\Greeting\TextGreeting;
use JeroenG\Autowire\Tests\Support\Subject\Domain\MarsClass;
use JeroenG\Autowire\Tests\Support\Subject\Domain\MoonClass;
use JeroenG\Autowire\Tests\Support\Subject\Domain\WorldClass;
use JeroenG\Autowire\Tests\Support\SubjectDirectory;
use PHPUnit\Framework\TestCase;

final class ElectricianTest extends TestCase
{
    public function test_it_can_tell_if_class_has_autowire_attribute(): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);
        $electrician = new Electrician($crawler);

        self::assertTrue($electrician->canAutowire(HelloInterface::class));
        self::assertFalse($electrician->canAutowire(GoodbyeInterface::class));
    }

    public function test_it_can_tell_if_class_has_configure_attribute(): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);
        $electrician = new Electrician($crawler);

        self::assertTrue($electrician->canConfigure(TextGreeting::class));
        self::assertFalse($electrician->canConfigure(GoodbyeInterface::class));
        self::assertFalse($electrician->canConfigure(WorldClass::class));
    }

    public function test_it_can_connect_implementation(): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);
        $electrician = new Electrician($crawler);

        $wire = $electrician->connect(HelloInterface::class);

        self::assertEquals(HelloInterface::class, $wire->interface);
        self::assertEquals(WorldClass::class, $wire->implementation);
    }

    public function test_it_throws_exception_when_implementation_not_found(): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);
        $electrician = new Electrician($crawler);

        $this->expectException(FaultyWiringException::class);
        $this->expectExceptionMessage('No implementation found for '.GoodbyeInterface::class);
        $electrician->connect(GoodbyeInterface::class);
    }

    /** @dataProvider configureDataProvider */
    public function test_it_can_configure_implementation_with_different_values(
        string $class,
        string $value,
        string $type,
    ): void
    {
        $crawler = Crawler::in([SubjectDirectory::GREETINGS]);
        $electrician = new Electrician($crawler);

        $configuration = $electrician->configure($class);

        $expected = [new ConfigurationValue('$greeting', $value, $type)];

        self::assertEquals($class, $configuration->implementation);
        self::assertEquals($expected, $configuration->definitions);
    }

    public static function configureDataProvider(): Generator
    {
        yield 'text' => [
            TextGreeting::class,
            'Good day to you!',
            ConfigurationType::UNKNOWN
        ];

        yield 'config' => [
            ConfigGreeting::class,
            'greeting.hi',
            ConfigurationType::CONFIG
        ];

        yield 'class' => [
            ClassGreeting::class,
            'App\Greeting',
            ConfigurationType::SERVICE
        ];
    }

    public function test_it_throws_exception_when_implementation_can_not_be_configured(): void
    {
        $crawler = Crawler::in([SubjectDirectory::CONTRACTS]);
        $electrician = new Electrician($crawler);

        $this->expectException(FaultyWiringException::class);
        $this->expectExceptionMessage('No JeroenG\Autowire\Attribute\Configure found in '.GoodbyeInterface::class);
        $electrician->configure(GoodbyeInterface::class);
    }
    
    /** @dataProvider invalidAutowireAttributeProvider */
    public function test_it_throws_an_exception_on_an_invalid_custom_autowire_attribute(string $invalidAttribute): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);

        $this->expectException(InvalidAttributeException::class);

        new Electrician($crawler, $invalidAttribute);
    }
    
    public function invalidAutowireAttributeProvider(): Generator
    {
        yield 'text' => [
            'Hello, World!',
        ];
        
        yield 'non-attribute class' => [
            HowDoYouDoInterface::class,
        ];
        
        yield 'wrong attribute class' => [
            CustomConfigure::class,
        ];
    }
    
    public function test_it_can_tell_if_class_has_custom_autowire_attribute(): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);
        $electrician = new Electrician($crawler, CustomAutowire::class);

        self::assertTrue($electrician->canAutowire(HowDoYouDoInterface::class));
        self::assertFalse($electrician->canAutowire(HelloInterface::class));
    }

    public function test_it_can_connect_implementation_with_custom_autowire_attribute(): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);
        $electrician = new Electrician($crawler, CustomAutowire::class);

        $wire = $electrician->connect(HowDoYouDoInterface::class);

        self::assertEquals(HowDoYouDoInterface::class, $wire->interface);
        self::assertEquals(MoonClass::class, $wire->implementation);
    }
    
    /** @dataProvider invalidConfigureAttributeProvider */
    public function test_it_throws_an_exception_on_an_invalid_custom_configure_attribute(string $invalidAttribute): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);

        $this->expectException(InvalidAttributeException::class);

        $electrician = new Electrician(crawler: $crawler, configureAttribute: $invalidAttribute);
    }
    
    public function invalidConfigureAttributeProvider(): Generator
    {
        yield 'text' => [
            'Hello, World!',
        ];
        
        yield 'non-attribute class' => [
            HowDoYouDoInterface::class,
        ];
        
        yield 'wrong attribute class' => [
            CustomAutowire::class,
        ];
    }

    public function test_it_can_tell_if_class_has_custom_listen_attribute(): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);
        $electrician = new Electrician($crawler, listenAttribute: CustomListen::class);

        self::assertTrue($electrician->canListen(CustomListener::class));
        self::assertFalse($electrician->canListen(HelloInterface::class));
    }

    public function test_it_can_tell_if_class_has_custom_configure_attribute(): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);
        $electrician = new Electrician(crawler: $crawler, configureAttribute: CustomConfigure::class);

        self::assertTrue($electrician->canConfigure(CustomGreeting::class));
        self::assertFalse($electrician->canConfigure(TextGreeting::class));
    }

    public function test_it_can_configure_implementation_with_custom_configure_attribute(): void
    {
        $crawler = Crawler::in([SubjectDirectory::GREETINGS]);
        $electrician = new Electrician(crawler: $crawler, configureAttribute: CustomConfigure::class);

        $configuration = $electrician->configure(CustomGreeting::class);

        $expected = [new ConfigurationValue('$greeting', 'Good day to you!', ConfigurationType::UNKNOWN)];

        self::assertEquals(CustomGreeting::class, $configuration->implementation);
        self::assertEquals($expected, $configuration->definitions);
    }

    public function test_it_can_retrieve_events_from_listener(): void
    {
        $crawler = Crawler::in([SubjectDirectory::GREETINGS]);
        $electrician = new Electrician($crawler, listenAttribute: CustomListen::class);

        $events = $electrician->events(CustomListener::class);

        self::assertEquals([
            MarsClass::class,
            MoonClass::class,
        ], $events);
    }

    public function test_it_throws_an_exception_on_an_invalid_custom_listen_attribute(): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);

        $this->expectException(InvalidAttributeException::class);

        new Electrician(crawler: $crawler, listenAttribute: HelloInterface::class);
    }

    public function test_it_throws_exception_on_a_class_missing_listeners(): void
    {
        $this->expectException(FaultyWiringException::class);
        $this->expectExceptionMessage('No JeroenG\Autowire\Tests\Support\Attributes\CustomListen found in JeroenG\Autowire\Tests\Support\Subject\Contracts\HelloInterface');

        $crawler = Crawler::in([SubjectDirectory::GREETINGS]);
        $electrician = new Electrician($crawler, listenAttribute: CustomListen::class);

        $electrician->events(HelloInterface::class);
    }
}
