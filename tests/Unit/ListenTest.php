<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Unit;

use JeroenG\Autowire\Attribute\Listen;
use JeroenG\Autowire\Exception\InvalidAttributeException;
use PHPUnit\Framework\TestCase;

final class ListenTest extends TestCase
{
    public function test_it_will_throw_with_invalid_class(): void
    {
        $this->expectException(InvalidAttributeException::class);
        $this->expectExceptionMessage('Class unknown-class does not exist');

        new Listen('unknown-class');
    }
}
