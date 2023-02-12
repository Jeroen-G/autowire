<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

use Webmozart\Assert\Assert;

final class TaggedInterface
{
    /** @var non-empty-string */
    public string $tag;

    /** @var list<class-string> */
    public array $implementations;

    public function __construct(string $tag, array $implementations)
    {
        Assert::notEmpty($tag, 'Tag may not be empty');
        Assert::allClassExists($implementations, 'Implementations must be valid classes');
        
        $this->tag = $tag;
        $this->implementations = $implementations;
    }
    
    public static function __set_state(array $properties): self
    {
        Assert::keyExists($properties, 'tag', 'Invalid export');
        Assert::keyExists($properties, 'implementations', 'Invalid export');
        
        return new self($properties['tag'], $properties['implementations']);
    }
}
