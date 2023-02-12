<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use JeroenG\Autowire\Attribute\Tag as TagAttribute;
use JeroenG\Autowire\Attribute\Autowire as AutowireAttribute;
use JeroenG\Autowire\Attribute\Configure as ConfigureAttribute;
use JeroenG\Autowire\Attribute\Listen as ListenAttribute;
use JeroenG\Autowire\Console\AutowireCacheCommand;
use JeroenG\Autowire\Console\AutowireClearCommand;
use JeroenG\Autowire\TaggedInterface;
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

        try {
            $cache = File::getRequire(App::bootstrapPath('cache/autowire.php'));
            $this->loadFromCache($cache);
        } catch (FileNotFoundException|JsonException) {
            $this->crawlAndLoad();
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

        array_walk($tagCache, function (TaggedInterface $taggedInterface): void {$this->app->tag($taggedInterface->implementations, $taggedInterface->tag);});
    }

    private function crawlAndLoad(): void
    {
        $crawler = Crawler::in(config('autowire.directories'));
        $autowireAttribute = config('autowire.autowire_attribute', AutowireAttribute::class);
        $configureAttribute = config('autowire.configure_attribute', ConfigureAttribute::class);
        $listenAttribute = config('autowire.listen_attribute', ListenAttribute::class);
        $tagAttribute = config('autowire.tag_attribute', TagAttribute::class);
        $electrician = new Electrician($crawler, $autowireAttribute, $configureAttribute, $listenAttribute, $tagAttribute,);

        $wires = $crawler->filter(fn (string $name) => $electrician->canAutowire($name))->classNames();
        $listeners = $crawler->filter(fn (string $name) => $electrician->canListen($name))->classNames();
        $configures = $crawler->filter(fn (string $name) => $electrician->canConfigure($name))->classNames();
        $taggables = $crawler->filter(fn (string $name) => $electrician->canTag($name))->classNames();

        foreach ($wires as $interface) {
            $wire = $electrician->connect($interface);
            $this->app->bindIf($wire->interface, $wire->implementation);
        }

        foreach ($listeners as $listener) {
            $events = $electrician->events($listener);

            Event::listen($events, $listener);
        }

        foreach ($configures as $implementation) {
            $configuration = $electrician->configure($implementation);

            foreach ($configuration->definitions as $definition) {
                $this->define($implementation, $definition);
            }
        }

        array_walk(
            $taggables,
            function (string $interface) use ($electrician): void {
                $taggedInterface = $electrician->tag($interface);
                $this->app->tag($taggedInterface->implementations, $taggedInterface->tag);
            },
        );
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
