<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Unit;

use JeroenG\Autowire\Crawler;
use JeroenG\Autowire\Electrician;
use JeroenG\Autowire\Exception\FaultyWiringException;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\GoodbyeInterface;
use JeroenG\Autowire\Tests\Support\Subject\Contracts\HelloInterface;
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
}
