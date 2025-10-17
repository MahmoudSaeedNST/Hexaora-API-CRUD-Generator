<?php

namespace Hexaora\CrudGenerator\Generators\ExtensionGenerators;

use Hexaora\CrudGenerator\Generators\BaseGenerator;
use Hexaora\CrudGenerator\Helpers\LinkerManager;
use Illuminate\Support\Str;

class PermissionSeederGenerator extends BaseGenerator
{
    /**
     * Generate permission seeder file (Spatie mode).
     */
    public function generate(string $entity, string $module, array $options = []): array
    {
        $common = $this->getCommonReplacements($entity, $module);
        
        $seederNamespace = str_replace(
            '{module}', 
            $module, 
            config('hexaora.seeders.namespace', "App\\Modules\\{module}\\Database\\Seeders")
        );
        
        $replacements = array_merge($common, [
            'namespace' => $seederNamespace,
            'class' => $entity,
            'model' => $entity,
            'modelLower' => Str::lower(Str::snake($entity)),
        ]);

        $content = $this->stubProcessor->getStubContent('permission-seeder', $replacements);
        $path = app_path("Modules/{$module}/Database/seeders/{$entity}PermissionSeeder.php");
        
        $this->fileManager->generateFile($path, $content, $options['force'] ?? false);
        
        // Append to global seeder linker
        LinkerManager::appendToGlobalSeederLinker($module, "{$entity}Permission", $seederNamespace);
        
        return ['type' => 'Permission Seeder', 'path' => $path];
    }

    /**
     * Check if permission seeder should be generated.
     */
    public function shouldGenerate(array $options): bool
    {
        $spatieEnabled = config('hexaora.policies.spatie', false);
        $seederRequested = ($options['seeder'] ?? false) || ($options['all'] ?? false);
        
        return $spatieEnabled && $seederRequested;
    }
}
