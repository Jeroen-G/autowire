<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Unit;

use JeroenG\Autowire\TaggedInterface;
use PHPUnit\Framework\TestCase;

final class TaggedInterfaceTest extends TestCase
{
    public function test_tag_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tag may not be empty');

        new TaggedInterface('', []);
    }
    
    public function test_all_implementations_must_be_valid_classes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Implementations must be valid classes');

        new TaggedInterface('test', ['not_a_class']);
    }
    
    public function test_an_exported_object_can_be_loaded(): void
    {
        $taggedInterface = TaggedInterface::__set_state([
               'tag' => 'testTag',
               'implementations' => [],
        ]);
        $this->assertEquals(new TaggedInterface('testTag', []), $taggedInterface);
    }
    
    public function test_an_export_without_a_tag_cant_be_read(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid export');
        
        $taggedInterface = TaggedInterface::__set_state([
               'implementations' => [],
        ]);
    }
    
    public function test_an_export_without_implementations_cant_be_read(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid export');
        
        $taggedInterface = TaggedInterface::__set_state([
               'tag' => 'testTag',
        ]);
    }
}
