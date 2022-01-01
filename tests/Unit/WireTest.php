<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Unit;

use JeroenG\Autowire\Wire;
use PHPUnit\Framework\TestCase;

final class WireTest extends TestCase
{
    public function test_it_can_read_properties(): void
    {
        $wire = new Wire('A', 'B');

        self::assertEquals('A', $wire->interface);
        self::assertEquals('B', $wire->implementation);
    }
}
