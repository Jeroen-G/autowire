<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use JeroenG\Autowire\CacheBuilder;

class AutowireCacheCommand extends Command
{
    protected $signature = 'autowire:cache';

    protected $description = 'Cache the autowiring and configurations.';

    public function handle(): int
    {
        $cache = CacheBuilder::factory()->build();

        File::put(
            App::bootstrapPath('cache/autowire.php'),
            '<?php return ' . var_export($cache, true) . ';' . PHP_EOL
        );

        $this->info('Autowire cache created!');
        return 0;
    }
}
