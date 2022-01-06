<?php declare(strict_types=1);

namespace JeroenG\Autowire\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use JeroenG\Autowire\Crawler;
use JeroenG\Autowire\Electrician;

class AutowireCacheCommand extends Command
{
    protected $signature = 'autowire:cache';

    protected $description = 'Cache the autowiring and configurations.';

    public function handle(): int
    {
        $crawler = Crawler::in(config('autowire.directories'));
        $electrician = new Electrician($crawler);

        $autowires = $crawler->filter(fn(string $name) => $electrician->canAutowire($name))->classNames();
        $configures = $crawler->filter(fn(string $name) => $electrician->canConfigure($name))->classNames();
        $autowireCache = [];
        $configureCache = [];

        foreach ($autowires as $interface) {
            $wire = $electrician->connect($interface);
            $autowireCache[$wire->interface] = $wire->implementation;
        }

        foreach ($configures as $implementation) {
            $configuration = $electrician->configure($implementation);

            foreach ($configuration->definitions as $definition) {
                $configureCache[$implementation] = [
                    'type' => $definition->type,
                    'need' => $definition->need,
                    'give' => $definition->give,
                ];
            }
        }

        $cache = [
            'autowire' => $autowireCache,
            'configure' => $configureCache,
        ];

        File::put(App::bootstrapPath('cache/autowire.json'), json_encode($cache, JSON_THROW_ON_ERROR));

        return 0;
    }
}
