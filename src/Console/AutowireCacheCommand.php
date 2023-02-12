<?php declare(strict_types=1);

namespace JeroenG\Autowire\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use JeroenG\Autowire\Attribute\Autowire as AutowireAttribute;
use JeroenG\Autowire\Attribute\Configure as ConfigureAttribute;
use JeroenG\Autowire\Attribute\Listen as ListenAttribute;
use JeroenG\Autowire\Crawler;
use JeroenG\Autowire\Electrician;
use JeroenG\Autowire\TaggedInterface;

class AutowireCacheCommand extends Command
{
    protected $signature = 'autowire:cache';

    protected $description = 'Cache the autowiring and configurations.';

    public function handle(): int
    {
        $crawler = Crawler::in(config('autowire.directories'));
        $autowireAttribute = config('autowire.autowire_attribute', AutowireAttribute::class);
        $configureAttribute = config('autowire.configure_attribute', ConfigureAttribute::class);
        $listenAttribute = config('autowire.listen_attribute', ListenAttribute::class);
        $autotagAttribute = config('autowire.autotag_attribute', AutotagAttribute::class);
        $electrician = new Electrician($crawler, $autowireAttribute, $configureAttribute, $listenAttribute, $autotagAttribute);

        $autowires = $crawler->filter(fn(string $name) => $electrician->canAutowire($name))->classNames();
        $listeners = $crawler->filter(fn(string $name) => $electrician->canListen($name))->classNames();
        $configures = $crawler->filter(fn(string $name) => $electrician->canConfigure($name))->classNames();
        $taggables = $crawler->filter(fn (string $name) => $electrician->canAutotag($name))->classNames();
        
        $autowireCache = [];
        $listenCache = [];
        $configureCache = [];

        foreach ($autowires as $interface) {
            $wire = $electrician->connect($interface);
            $autowireCache[$wire->interface] = $wire->implementation;
        }

        foreach ($listeners as $listener) {
            $listenCache[$listener] = $electrician->events($listener);
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
        
        $autotagCache = array_values(array_map(fn (string $interface): TaggedInterface => $electrician->tag($interface), $taggables));

        $cache = [
            'autowire' => $autowireCache,
            'listen' => $listenCache,
            'configure' => $configureCache,
            'autotag' => $autotagCache,
        ];

        File::put(
            App::bootstrapPath('cache/autowire.php'),
            '<?php return '.var_export($cache, true).';'.PHP_EOL
        );

        $this->info('Autowire cache created!');
        return 0;
    }
}
