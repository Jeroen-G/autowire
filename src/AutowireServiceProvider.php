<?php

declare(strict_types=1);

namespace JeroenG\Autowire;

use Illuminate\Support\ServiceProvider;

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

        $crawler = Crawler::in(config('autowire.directories'));
        $electrician = new Electrician($crawler);

        $wires = $crawler->filter(fn(string $name) => $electrician->canAutowire($name))->classNames();

        foreach ($wires as $interface) {
            $wire = $electrician->connect($interface);
            $this->app->bindIf($wire->interface, $wire->implementation);
        }
    }

    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__.'/../config/autowire.php' => config_path('autowire.php'),
        ], 'autowire.config');

        // Registering package commands.
        // $this->commands([]);
    }
}
