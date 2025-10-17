<?php

namespace Hexaora\CrudGenerator\Generators\ExtensionGenerators;

use Hexaora\CrudGenerator\Generators\BaseGenerator;
use Hexaora\CrudGenerator\Helpers\FieldMapper;
use Hexaora\CrudGenerator\Helpers\LinkerManager;

class FactoryGenerator extends BaseGenerator
{
    /**
     * Generate factory file.
     */
    public function generate(string $entity, string $module, array $options = []): array
    {
        $common = $this->getCommonReplacements($entity, $module);
        
        $factoryNamespace = str_replace(
            '{module}', 
            $module, 
            config('hexaora.factories.namespace', "App\\Modules\\{module}\\Database\\Factories")
        );
        
        // Generate factory fields with intelligent faker mapping
        $factoryFields = FieldMapper::generateFactoryFields($this->fields);
        
        $replacements = array_merge($common, [
            'namespace' => $factoryNamespace,
            'class' => $entity,
            'modelNamespace' => $common['modelNamespace'],
            'model' => $entity,
            'factoryFields' => $factoryFields,
        ]);

        $content = $this->stubProcessor->getStubContent('factory', $replacements);
        $path = app_path("Modules/{$module}/Database/factories/{$entity}Factory.php");
        
        $this->fileManager->generateFile($path, $content, $options['force'] ?? false);
        
        // Update global api_factories.php linker
        LinkerManager::ensureGlobalFactoryLinkerExists();
        LinkerManager::appendToGlobalFactoryLinker($module, $entity);
        
        return ['type' => 'Factory', 'path' => $path];
    }

    /**
     * Check if factory should be generated.
     */
    public function shouldGenerate(array $options): bool
    {
        return ($options['factory'] ?? false) || ($options['all'] ?? false);
    }
}
