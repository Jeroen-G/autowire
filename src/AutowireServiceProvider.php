<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use JeroenG\Autowire\Console\AutowireCacheCommand;
use JeroenG\Autowire\Console\AutowireClearCommand;
use JeroenG\Autowire\Testing\CachedState;
use JsonException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class AutowireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/autowire.php', 'autowire');

        if (CachedState::$cachedAutowire !== null) {
            $this->loadFromCache(CachedState::$cachedAutowire);

            return;
        }

        try {
            $cache = File::getRequire(App::bootstrapPath('cache/autowire.php'));
            $this->loadFromCache($cache);
        } catch (FileNotFoundException|JsonException) {
            $cache = CacheBuilder::factory()->build();
            $this->loadFromCache($cache);
        }
    }

    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__ . '/../config/autowire.php' => config_path('autowire.php'),
        ], 'autowire.config');

        $this->commands([
            AutowireCacheCommand::class,
            AutowireClearCommand::class,
        ]);
    }

    private function loadFromCache(array $cache): void
    {
        $autowireCache = $cache['autowire'] ?? [];
        $listenCache = $cache['listen'] ?? [];
        $configureCache = $cache['configure'] ?? [];
        $tagCache = $cache['tag'] ?? [];

        foreach ($autowireCache as $interface => $implementation) {
            $this->app->bindIf($interface, $implementation);
        }

        foreach ($listenCache as $listener => $events) {
            Event::listen($events, $listener);
        }

        foreach ($configureCache as $implementation => $details) {
            $definition = new ConfigurationValue(
                need: $details['need'],
                give: $details['give'],
                type: $details['type'],
            );

            $this->define($implementation, $definition);
        }

        array_walk($tagCache, function (TaggedInterface $taggedInterface): void {
            $this->app->tag($taggedInterface->implementations, $taggedInterface->tag);
        });
    }

    private function define(string $implementation, ConfigurationValue $definition): void
    {
        switch ($definition->type) {
            case ConfigurationType::CONFIG:
                $this->app->when($implementation)->needs($definition->need)->giveConfig($definition->give);
                break;
            case ConfigurationType::SERVICE:
                $give = $this->app->make($definition->give);
                $this->app->when($implementation)->needs($definition->need)->give($give);
                break;
            case ConfigurationType::TAGGED:
                $this->app->when($implementation)->needs($definition->need)->giveTagged($definition->give);
                break;
            case ConfigurationType::UNKNOWN:
            default:
                $this->app->when($implementation)->needs($definition->need)->give($definition->give);
        }
    }
}
