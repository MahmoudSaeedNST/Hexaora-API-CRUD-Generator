<?php

namespace Hexaora\CrudGenerator\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LinkerManager
{
    /**
     * Ensure global factory linker file exists.
     */
    public static function ensureGlobalFactoryLinkerExists(): void
    {
        $path = database_path('api_factories.php');
        
        if (!File::exists($path)) {
            $content = "<?php\n\n/**\n * API Factories linker for all modules.\n * Automatically updated by Hexaora Generator.\n */\n\n";
            File::put($path, $content);
        }
    }

    /**
     * Append factory to global linker.
     */
    public static function appendToGlobalFactoryLinker(string $module, string $entity): void
    {
        $path = database_path('api_factories.php');
        $factoryPath = "app/Modules/{$module}/Database/factories/{$entity}Factory.php";
        $requireStatement = "require base_path('{$factoryPath}');\n";
        
        $content = File::get($path);
        
        if (Str::contains($content, $factoryPath)) {
            return;
        }
        
        File::append($path, $requireStatement);
    }

    /**
     * Ensure global seeder linker file exists.
     */
    public static function ensureGlobalSeederLinkerExists(): void
    {
        $path = database_path('api_seeders.php');
        
        if (!File::exists($path)) {
            $content = "<?php\n\n/**\n * Central seeder registry for all Hexaora modules.\n */\n\nreturn [\n    // Auto-registered by Hexaora Generator\n];\n";
            File::put($path, $content);
        }
    }

    /**
     * Append seeder to global linker.
     */
    public static function appendToGlobalSeederLinker(string $module, string $entity, string $namespace): void
    {
        $path = database_path('api_seeders.php');
        $seederClass = "{$namespace}\\{$entity}Seeder";
        
        $content = File::get($path);
        
        if (Str::contains($content, $seederClass)) {
            return;
        }
        
        $content = str_replace(
            '];',
            "    {$seederClass}::class,\n];",
            $content
        );
        
        File::put($path, $content);
    }

    /**
     * Ensure ApiSeeder master file exists.
     */
    public static function ensureApiSeederExists(): void
    {
        $path = database_path('seeders/ApiSeeder.php');
        
        if (File::exists($path)) {
            return;
        }
        
        $content = <<<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seeders = require database_path('api_seeders.php');
        
        foreach ($seeders as $seeder) {
            $this->call($seeder);
        }
    }
}
PHP;
        
        if (!File::exists(database_path('seeders'))) {
            File::makeDirectory(database_path('seeders'), 0755, true);
        }
        
        File::put($path, $content);
    }

    /**
     * Ensure AuthServiceProvider exists.
     */
    public static function ensureAuthServiceProviderExists($stubProcessor, $fileManager): void
    {
        $path = app_path('Providers/AuthServiceProvider.php');
        
        if (!File::exists($path)) {
            $content = $stubProcessor->getStubContent('auth-provider', []);
            $fileManager->generateFile($path, $content, false);
        }
    }

    /**
     * Append policy mapping to AuthServiceProvider.
     */
    public static function appendToAuthServiceProvider(string $modelClass, string $policyClass): void
    {
        $path = app_path('Providers/AuthServiceProvider.php');
        
        if (!File::exists($path)) {
            return;
        }
        
        $content = File::get($path);
        
        if (Str::contains($content, $modelClass)) {
            return;
        }
        
        $policyEntry = "        {$modelClass}::class => {$policyClass}::class,";
        
        if (Str::contains($content, '// Auto-registered by Hexaora CRUD Generator')) {
            $content = str_replace(
                '// Auto-registered by Hexaora CRUD Generator',
                "// Auto-registered by Hexaora CRUD Generator\n{$policyEntry}",
                $content
            );
        } else {
            $content = preg_replace(
                '/(\$policies\s*=\s*\[.*?)(\];)/s',
                "$1    {$policyEntry}\n$2",
                $content
            );
        }
        
        File::put($path, $content);
    }
}
