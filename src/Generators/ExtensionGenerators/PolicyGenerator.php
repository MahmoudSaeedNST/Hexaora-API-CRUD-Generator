<?php

namespace Hexaora\CrudGenerator\Generators\ExtensionGenerators;

use Hexaora\CrudGenerator\Generators\BaseGenerator;
use Hexaora\CrudGenerator\Helpers\LinkerManager;
use Illuminate\Support\Str;

class PolicyGenerator extends BaseGenerator
{
    /**
     * Generate policy file.
     */
    public function generate(string $entity, string $module, array $options = []): array
    {
        $common = $this->getCommonReplacements($entity, $module);
        
        // Determine stub based on configuration
        $stubName = config('hexaora.policies.spatie', false) ? 'policy-spatie' : 'policy';
        
        $policyNamespace = str_replace(
            '{module}', 
            $module, 
            config('hexaora.policies.namespace', "App\\Modules\\{module}\\Domain\\Policies")
        );
        
        $replacements = array_merge($common, [
            'namespace' => $policyNamespace,
            'class' => $entity,
            'modelNamespace' => $common['modelNamespace'],
            'model' => $entity,
            'modelVariable' => Str::camel($entity),
            'modelLower' => Str::lower(Str::snake($entity)),
        ]);

        $content = $this->stubProcessor->getStubContent($stubName, $replacements);
        $path = app_path("Modules/{$module}/Domain/Policies/{$entity}Policy.php");
        
        $this->fileManager->generateFile($path, $content, $options['force'] ?? false);
        
        // Register policy in AuthServiceProvider if auto_register is enabled
        if (config('hexaora.policies.auto_register', true)) {
            LinkerManager::ensureAuthServiceProviderExists($this->stubProcessor, $this->fileManager);
            LinkerManager::appendToAuthServiceProvider(
                $common['modelNamespace'], 
                "{$policyNamespace}\\{$entity}Policy"
            );
        }
        
        return ['type' => 'Policy', 'path' => $path];
    }

    /**
     * Check if policy should be generated.
     */
    public function shouldGenerate(array $options): bool
    {
        return ($options['policy'] ?? false) || ($options['all'] ?? false);
    }
}
