<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Unit;

use JeroenG\Autowire\Crawler;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodafternoonInterface;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodbyeInterface;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodeveningInterface;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodmorningInterface;
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

final class CrawlerTest extends TestCase
{
    public function test_it_can_construct_list_of_files(): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL]);

        $expected = [
            GoodafternoonInterface::class,
            GoodbyeInterface::class,
            GoodeveningInterface::class,
            GoodmorningInterface::class,
            HelloInterface::class,
            HowDoYouDoInterface::class,
            CustomListener::class,
            ClassGreeting::class,
            ConfigGreeting::class,
            CustomGreeting::class,
            TextGreeting::class,
            MarsClass::class,
            MoonClass::class,
            WorldClass::class,
        ];

        self::assertEqualsCanonicalizing($expected, $crawler->classNames());
    }

    public function test_it_can_filter(): void
    {
        $crawler = Crawler::in([SubjectDirectory::ALL])
            ->filter(fn(string $class) => !str_contains($class, 'Greeting'));

        $expected = [
            GoodafternoonInterface::class,
            GoodbyeInterface::class,
            GoodeveningInterface::class,
            GoodmorningInterface::class,
            HelloInterface::class,
            HowDoYouDoInterface::class,
            CustomListener::class,
            MarsClass::class,
            MoonClass::class,
            WorldClass::class,
        ];

        self::assertEqualsCanonicalizing($expected, array_values($crawler->classNames()));
    }
}
