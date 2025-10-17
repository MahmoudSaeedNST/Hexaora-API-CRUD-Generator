<?php

namespace Hexaora\CrudGenerator\Generators;

use Hexaora\CrudGenerator\Generators\Contracts\GeneratorInterface;
use Illuminate\Support\Str;

abstract class BaseGenerator implements GeneratorInterface
{
    /**
     * The stub processor instance.
     */
    protected $stubProcessor;

    /**
     * The file manager instance.
     */
    protected $fileManager;

    /**
     * Parsed fields from command.
     */
    protected array $fields = [];

    /**
     * Constructor.
     */
    public function __construct($stubProcessor, $fileManager, array $fields = [])
    {
        $this->stubProcessor = $stubProcessor;
        $this->fileManager = $fileManager;
        $this->fields = $fields;
    }

    /**
     * Get common replacements for stubs.
     */
    protected function getCommonReplacements(string $entity, string $module, ?string $apiVersion = null): array
    {
        $moduleNamespace = config('hexaora.module_namespace', 'App\\Modules');
        $rootNamespace = "{$moduleNamespace}\\{$module}";

        return [
            'rootNamespace' => $rootNamespace,
            'moduleNamespace' => $moduleNamespace,
            'module' => $module,
            'entity' => $entity,
            'entityPlural' => Str::plural($entity),
            'entityLower' => Str::lower($entity),
            'entityLowerPlural' => Str::plural(Str::lower($entity)),
            'entityCamel' => Str::camel($entity),
            'entitySnake' => Str::snake($entity),
            'table' => Str::plural(Str::snake($entity)),
            'modelNamespace' => "{$rootNamespace}\\Domain\\Models\\{$entity}",
            'moduleNameLower' => Str::lower($module),
            'apiVersion' => $apiVersion,
            'apiVersionNamespace' => $apiVersion ? Str::studly($apiVersion) : null,
        ];
    }

    /**
     * Check if generator should run by default.
     */
    public function shouldGenerate(array $options): bool
    {
        return true; // Override in child classes if needed
    }
}
