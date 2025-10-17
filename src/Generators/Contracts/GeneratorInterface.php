<?php

namespace Hexaora\CrudGenerator\Generators\Contracts;

interface GeneratorInterface
{
    /**
     * Generate the file(s) for this generator.
     *
     * @param string $entity
     * @param string $module
     * @param array $options
     * @return array Returns ['type' => string, 'path' => string]
     */
    public function generate(string $entity, string $module, array $options = []): array;

    /**
     * Check if this generator should run based on options.
     *
     * @param array $options
     * @return bool
     */
    public function shouldGenerate(array $options): bool;
}
