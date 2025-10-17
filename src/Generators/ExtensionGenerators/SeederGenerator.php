<?php

namespace Hexaora\CrudGenerator\Generators\ExtensionGenerators;

use Hexaora\CrudGenerator\Generators\BaseGenerator;
use Hexaora\CrudGenerator\Helpers\LinkerManager;

class SeederGenerator extends BaseGenerator
{
    /**
     * Generate seeder file.
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
            'modelNamespace' => $common['modelNamespace'],
            'model' => $entity,
            'count' => config('hexaora.factories.count', 10),
        ]);

        $content = $this->stubProcessor->getStubContent('seeder', $replacements);
        $path = app_path("Modules/{$module}/Database/seeders/{$entity}Seeder.php");
        
        $this->fileManager->generateFile($path, $content, $options['force'] ?? false);
        
        // Update global api_seeders.php linker
        LinkerManager::ensureGlobalSeederLinkerExists();
        LinkerManager::appendToGlobalSeederLinker($module, $entity, $seederNamespace);
        
        // Ensure ApiSeeder exists
        LinkerManager::ensureApiSeederExists();
        
        return ['type' => 'Seeder', 'path' => $path];
    }

    /**
     * Check if seeder should be generated.
     */
    public function shouldGenerate(array $options): bool
    {
        return ($options['seeder'] ?? false) || ($options['all'] ?? false);
    }
}
