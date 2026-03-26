<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Testing;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use JeroenG\Autowire\CacheBuilder;

trait WithCachedAutowire
{
    public function setUpWithCachedAutowire(): void
    {
        if (File::exists(App::bootstrapPath('cache/autowire.php'))) {
            return;
        }

        if (CachedState::$cachedAutowire === null) {
            $cache = CacheBuilder::factory()->build();

            CachedState::$cachedAutowire = $cache;
        }
    }
}
