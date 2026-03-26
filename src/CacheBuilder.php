<?php declare(strict_types=1);

namespace JeroenG\Autowire;

use JeroenG\Autowire\Attribute\Autowire as AutowireAttribute;
use JeroenG\Autowire\Attribute\Configure as ConfigureAttribute;
use JeroenG\Autowire\Attribute\Listen as ListenAttribute;
use JeroenG\Autowire\Attribute\Tag as TagAttribute;

final class CacheBuilder
{
    private function __construct() {}

    public static function factory(): self
    {
        return new self();
    }

    public function build(): array {
        $crawler = Crawler::in(config('autowire.directories'));
        $autowireAttribute = config('autowire.autowire_attribute', AutowireAttribute::class);
        $configureAttribute = config('autowire.configure_attribute', ConfigureAttribute::class);
        $listenAttribute = config('autowire.listen_attribute', ListenAttribute::class);
        $tagAttribute = config('autowire.tag_attribute', TagAttribute::class);
        $electrician = new Electrician($crawler, $autowireAttribute, $configureAttribute, $listenAttribute, $tagAttribute);

        $autowires = $crawler->filter(fn(string $name) => $electrician->canAutowire($name))->classNames();
        $listeners = $crawler->filter(fn(string $name) => $electrician->canListen($name))->classNames();
        $configures = $crawler->filter(fn(string $name) => $electrician->canConfigure($name))->classNames();
        $taggables = $crawler->filter(fn (string $name) => $electrician->canTag($name))->classNames();

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

        $tagCache = array_values(array_map(fn (string $interface): TaggedInterface => $electrician->tag($interface), $taggables));

        return [
            'autowire' => $autowireCache,
            'listen' => $listenCache,
            'configure' => $configureCache,
            'tag' => $tagCache,
        ];
    }
}