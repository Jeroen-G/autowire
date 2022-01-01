<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

final class Wire
{
    public function __construct(
        public string $interface,
        public string $implementation
    ){
    }
}
