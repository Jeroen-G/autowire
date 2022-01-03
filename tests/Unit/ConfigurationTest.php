<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Unit;

use JeroenG\Autowire\Configuration;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    public function test_it_can_only_contain_value_objects(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an instance of JeroenG\Autowire\ConfigurationValue. Got: boolean');

        new Configuration('foobar', [false]);
    }
}
