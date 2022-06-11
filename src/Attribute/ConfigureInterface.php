<?php

declare(strict_types=1);

namespace JeroenG\Autowire\Attribute;

interface ConfigureInterface
{
    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function getConfigs(): array;

    /**
     * @return array<non-empty-string, class-string>
     */
    public function getServices(): array;

    /**
     * @return array<non-empty-string, scalar>
     */
    public function getDefinitions(): array;
}
