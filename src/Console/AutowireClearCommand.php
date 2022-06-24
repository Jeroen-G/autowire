<?php declare(strict_types=1);

namespace JeroenG\Autowire\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

class AutowireClearCommand extends Command
{
    protected $signature = 'autowire:clear';

    protected $description = 'Clear the cache.';

    public function handle(): int
    {
        $deleted = File::delete(App::bootstrapPath('cache/autowire.php'));

        if (!$deleted) {
            $this->error('Could not clear cache.');
            return 1;
        }

        $this->info('Autowire cache cleared!');
        return 0;
    }
}
