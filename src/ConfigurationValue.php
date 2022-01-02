<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

final class ConfigurationValue
{
    public function __construct(
        public string $need,
        public mixed $give,
        public string $type,
    ){
    }
}
