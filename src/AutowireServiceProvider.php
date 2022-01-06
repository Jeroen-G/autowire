<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use JeroenG\Autowire\Console\AutowireCacheCommand;
use JeroenG\Autowire\Console\AutowireClearCommand;
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
        $this->mergeConfigFrom(__DIR__.'/../config/autowire.php', 'autowire');

        try {
            $cache = File::getRequire(App::bootstrapPath('cache/autowire.php'));
            $this->loadFromCache($cache);
        } catch (FileNotFoundException | JsonException) {
            $this->crawlAndLoad();
        }
    }

    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__.'/../config/autowire.php' => config_path('autowire.php'),
        ], 'autowire.config');

         $this->commands([
             AutowireCacheCommand::class,
             AutowireClearCommand::class,
         ]);
    }

    private function loadFromCache(array $cache): void
    {
        $autowireCache = $cache['autowire'] ?? [];
        $configureCache = $cache['configure'] ?? [];

        foreach ($autowireCache as $interface => $implementation) {
            $this->app->bindIf($interface, $implementation);
        }

        foreach ($configureCache as $implementation => $details) {
            $definition = new ConfigurationValue(
                need: $details['need'],
                give: $details['give'],
                type: $details['type'],
            );

            $this->define($implementation, $definition);
        }
    }

    private function crawlAndLoad(): void
    {
        $crawler = Crawler::in(config('autowire.directories'));
        $electrician = new Electrician($crawler);

        $wires = $crawler->filter(fn(string $name) => $electrician->canAutowire($name))->classNames();
        $configures = $crawler->filter(fn(string $name) => $electrician->canConfigure($name))->classNames();

        foreach ($wires as $interface) {
            $wire = $electrician->connect($interface);
            $this->app->bindIf($wire->interface, $wire->implementation);
        }

        foreach ($configures as $implementation) {
            $configuration = $electrician->configure($implementation);

            foreach ($configuration->definitions as $definition) {
                $this->define($implementation, $definition);
            }
        }
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
            case ConfigurationType::UNKNOWN:
            default:
                $this->app->when($implementation)->needs($definition->need)->give($definition->give);
        }
    }
}
