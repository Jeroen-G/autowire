<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Tests\Support\Subject\Domain\Greeting;

use JeroenG\Autowire\Attribute\Configure;

#[Configure([
    '$greeting' => '%greeting.hi%',
    '$count' => '5',
    '$logger' => '@Psr\Log\LoggerInterface',
    '$handlers' => '#handlers',
])]
class MultipleConfigGreeting
{
    public function __construct(
        public $greeting,
        public $count,
        public $logger,
        public $handlers
    ) {
    }
}
