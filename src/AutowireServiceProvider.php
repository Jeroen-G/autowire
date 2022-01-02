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

        $autowires = $crawler->filter(fn(string $name) => $electrician->canAutowire($name))->classNames();
        $configures = $crawler->filter(fn(string $name) => $electrician->canConfigure($name))->classNames();

        foreach ($configures as $implementation) {
            $configuration = $electrician->configure($implementation);

            foreach ($configuration->definitions as $definition) {
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

        foreach ($autowires as $interface) {
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
