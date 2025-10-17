<?php

namespace Hexaora\CrudGenerator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Hexaora\CrudGenerator\Generators\ExtensionGenerators\PolicyGenerator;
use Hexaora\CrudGenerator\Generators\ExtensionGenerators\FactoryGenerator;
use Hexaora\CrudGenerator\Generators\ExtensionGenerators\SeederGenerator;
use Hexaora\CrudGenerator\Generators\ExtensionGenerators\PermissionSeederGenerator;
use Hexaora\CrudGenerator\Services\StubProcessor;
use Hexaora\CrudGenerator\Services\FileManager;

class MakeHexaoraCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:hexaora {name : The name of the entity}
                            {--module= : The module name (required)}
                            {--fields= : Comma-separated field definitions}
                            {--api-version= : API version (e.g., v1)}
                            {--no-pagination : Disable pagination}
                            {--softdeletes : Add soft deletes}
                            {--policy : Generate policy class}
                            {--factory : Generate factory class}
                            {--seeder : Generate seeder class}
                            {--all : Generate everything (policy, factory, seeder)}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a complete CRUD API with Clean Architecture structure';

    /**
     * Parsed fields from --fields option
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Validate required options
        if (!$this->option('module')) {
            $this->error('The --module option is required.');
            return 1;
        }

        $entity = $this->argument('name');
        $module = $this->option('module');
        $apiVersion = $this->option('api-version');
        $noPagination = $this->option('no-pagination');
        $softDeletes = $this->option('softdeletes');
        $force = $this->option('force');
        
        // Handle --all flag
        $generateAll = $this->option('all');
        $generatePolicy = $this->option('policy') || $generateAll;
        $generateFactory = $this->option('factory') || $generateAll;
        $generateSeeder = $this->option('seeder') || $generateAll;

        // Parse fields
        if ($this->option('fields')) {
            $this->fields = $this->parseFields($this->option('fields'));
        }

        $this->info('');
        $this->info('Hexaora CRUD Generator');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('');

        // Generate all files
        $generatedFiles = [];

        try {
            // 1. Generate Model
            $generatedFiles[] = $this->generateModel($entity, $module, $softDeletes);

            // 2. Generate Repository Interface
            $generatedFiles[] = $this->generateRepositoryInterface($entity, $module, $noPagination);

            // 3. Generate Repository Implementation
            $generatedFiles[] = $this->generateRepository($entity, $module, $noPagination);

            // 4. Generate API Resource
            $generatedFiles[] = $this->generateResource($entity, $module);

            // 5. Generate Service
            $generatedFiles[] = $this->generateService($entity, $module, $noPagination);

            // 6. Generate Store Request
            $generatedFiles[] = $this->generateStoreRequest($entity, $module);

            // 7. Generate Update Request
            $generatedFiles[] = $this->generateUpdateRequest($entity, $module);

            // 8. Generate Controller
            $generatedFiles[] = $this->generateController($entity, $module, $apiVersion, $noPagination);

            // 9. Generate Migration
            $generatedFiles[] = $this->generateMigration($entity, $softDeletes);

            // 10. Generate Route File
            $generatedFiles[] = $this->generateRouteFile($entity, $module, $apiVersion);

            // 11. Generate Policy (optional) - Using new architecture
            if ($generatePolicy) {
                $generatedFiles[] = $this->generateWithGenerator(
                    PolicyGenerator::class,
                    $entity,
                    $module,
                    ['policy' => true, 'force' => $force]
                );
            }

            // 12. Generate Factory (optional) - Using new architecture
            if ($generateFactory) {
                $generatedFiles[] = $this->generateWithGenerator(
                    FactoryGenerator::class,
                    $entity,
                    $module,
                    ['factory' => true, 'force' => $force]
                );
            }

            // 13. Generate Seeder (optional) - Using new architecture
            if ($generateSeeder) {
                $generatedFiles[] = $this->generateWithGenerator(
                    SeederGenerator::class,
                    $entity,
                    $module,
                    ['seeder' => true, 'force' => $force]
                );
            }

            // 14. Generate Permission Seeder (optional, Spatie mode) - Using new architecture
            if ($generateSeeder && config('hexaora.policies.spatie', false)) {
                $generatedFiles[] = $this->generateWithGenerator(
                    PermissionSeederGenerator::class,
                    $entity,
                    $module,
                    ['seeder' => true, 'force' => $force]
                );
            }

            // Display success messages
            foreach ($generatedFiles as $file) {
                $this->info("✓ {$file['type']} created: {$file['path']}");
            }

            $this->info('');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('');
            $this->info('Next Steps:');
            $this->line('1. Run migration: php artisan migrate');
            $this->line('2. Bind repository in app/Providers/AppServiceProvider.php:');
            $this->info('');
            $this->line("   \$this->app->bind(");
            $this->line("       \\App\\Modules\\{$module}\\Domain\\Repositories\\{$entity}RepositoryInterface::class,");
            $this->line("       \\App\\Modules\\{$module}\\Infrastructure\\Repositories\\{$entity}Repository::class");
            $this->line("   );");
            $this->info('');

            $modulePrefix = Str::lower($module);
            $entityPlural = Str::plural(Str::lower($entity));
            $apiPath = $apiVersion ? "/api/{$apiVersion}/{$modulePrefix}/{$entityPlural}" : "/api/{$modulePrefix}/{$entityPlural}";
            $this->line("3. Your API is ready at: GET {$apiPath}");
            $this->info('');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('');

            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate using a Generator class (new architecture)
     */
    protected function generateWithGenerator(string $generatorClass, string $entity, string $module, array $options): array
    {
        $stubProcessor = new StubProcessor();
        $fileManager = new FileManager($options['force'] ?? false);
        
        $generator = new $generatorClass($stubProcessor, $fileManager, $this->fields);
        
        return $generator->generate($entity, $module, $options);
    }

    /**
     * Parse fields from command option
     */
    protected function parseFields(string $fieldsString): array
    {
        $fields = [];
        
        // First, we need to handle decimal types specially because they contain commas
        $fieldsString = preg_replace_callback(
            '/decimal:(\d+),(\d+)/',
            function($matches) {
                return "decimal:{$matches[1]}#{$matches[2]}";
            },
            $fieldsString
        );
        
        $fieldDefinitions = explode(',', $fieldsString);

        foreach ($fieldDefinitions as $definition) {
            $parts = explode(':', trim($definition));
            $fieldName = trim($parts[0]);
            $fieldType = trim($parts[1] ?? 'string');
            $modifiers = array_slice($parts, 2);
            
            // Restore decimal parameters
            if ($fieldType === 'decimal' && !empty($modifiers) && strpos($modifiers[0], '#') !== false) {
                $decimalParts = explode('#', $modifiers[0]);
                $modifiers = array_merge($decimalParts, array_slice($modifiers, 1));
            }

            $fields[] = [
                'name' => $fieldName,
                'type' => $fieldType,
                'modifiers' => $modifiers,
            ];
        }

        return $fields;
    }

    /**
     * Get stub content and replace placeholders
     */
    protected function getStubContent(string $stubName, array $replacements): string
    {
        $stubPath = base_path("stubs/hexaora/{$stubName}.stub");

        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found: {$stubPath}");
        }

        $content = File::get($stubPath);

        foreach ($replacements as $search => $replace) {
            $content = str_replace("{{ {$search} }}", $replace, $content);
        }

        return $content;
    }

    /**
     * Generate a file from stub
     */
    protected function generateFile(string $path, string $content, bool $force = false): void
    {
        if (File::exists($path) && !$force) {
            if (!$this->confirm("File {$path} already exists. Overwrite?")) {
                return;
            }
        }

        $directory = dirname($path);
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($path, $content);
    }

    /**
     * Get common replacements for stubs
     */
    protected function getCommonReplacements(string $entity, string $module, ?string $apiVersion = null): array
    {
        $rootNamespace = "App\\Modules\\{$module}";
        $variable = Str::camel($entity);
        $pluralVariable = Str::plural($variable);
        $table = Str::plural(Str::snake($entity));
        $apiVersionNamespace = $apiVersion ? Str::studly($apiVersion) : '';

        return [
            'rootNamespace' => $rootNamespace,
            'class' => $entity,
            'variable' => $variable,
            'pluralVariable' => $pluralVariable,
            'moduleName' => $module,
            'moduleNameLower' => Str::lower($module),
            'table' => $table,
            'apiVersion' => $apiVersion ?? '',
            'apiVersionNamespace' => $apiVersionNamespace,
            'modelNamespace' => "{$rootNamespace}\\Domain\\Models",
        ];
    }

    /**
     * Generate fillable fields array
     */
    protected function getFillableFields(): string
    {
        if (empty($this->fields)) {
            return "'name'";
        }

        $fillable = array_map(function ($field) {
            return "'{$field['name']}'";
        }, $this->fields);

        return implode(', ', $fillable);
    }

    /**
     * Generate migration fields
     */
    protected function getMigrationFields(): string
    {
        if (empty($this->fields)) {
            return "\$table->string('name');";
        }

        $lines = [];
        foreach ($this->fields as $field) {
            $line = $this->generateMigrationField($field);
            $lines[] = "            {$line}";
        }

        return implode("\n", $lines);
    }

    /**
     * Generate a single migration field line
     */
    protected function generateMigrationField(array $field): string
    {
        $name = $field['name'];
        $type = $field['type'];
        $modifiers = $field['modifiers'];

        switch ($type) {
            case 'string':
                $line = "\$table->string('{$name}')";
                break;
            case 'text':
                $line = "\$table->text('{$name}')";
                break;
            case 'integer':
                $line = "\$table->integer('{$name}')";
                break;
            case 'boolean':
                $line = "\$table->boolean('{$name}')";
                break;
            case 'decimal':
                $precision = $modifiers[0] ?? 10;
                $scale = $modifiers[1] ?? 2;
                $line = "\$table->decimal('{$name}', {$precision}, {$scale})";
                $modifiers = array_slice($modifiers, 2);
                break;
            case 'timestamp':
                $line = "\$table->timestamp('{$name}')->nullable()";
                break;
            case 'date':
                $line = "\$table->date('{$name}')";
                break;
            case 'foreignId':
                $line = "\$table->foreignId('{$name}')->constrained()";
                
                if (in_array('cascade', $modifiers)) {
                    $line .= "->cascadeOnDelete()";
                } elseif (in_array('nullOnDelete', $modifiers)) {
                    $line = "\$table->foreignId('{$name}')->nullable()->constrained()->nullOnDelete()";
                } elseif (in_array('restrict', $modifiers)) {
                    $line .= "->restrictOnDelete()";
                }
                $modifiers = array_diff($modifiers, ['cascade', 'nullOnDelete', 'restrict']);
                break;
            default:
                $line = "\$table->string('{$name}')";
        }

        if (in_array('unique', $modifiers)) {
            $line .= "->unique()";
        }

        return $line . ";";
    }

    /**
     * Generate validation rules
     */
    protected function getValidationRules(bool $isUpdate = false): string
    {
        if (empty($this->fields)) {
            return "'name' => 'required|string|max:255'";
        }

        $rules = [];
        foreach ($this->fields as $field) {
            $rule = $this->generateValidationRule($field, $isUpdate);
            $rules[] = "'{$field['name']}' => '{$rule}'";
        }

        return implode(",\n            ", $rules);
    }

    /**
     * Generate a single validation rule
     */
    protected function generateValidationRule(array $field, bool $isUpdate = false): string
    {
        $name = $field['name'];
        $type = $field['type'];
        $modifiers = $field['modifiers'];

        $required = $isUpdate ? 'sometimes|required' : 'required';
        $rules = [$required];

        switch ($type) {
            case 'string':
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;
            case 'text':
                $rules[] = 'string';
                break;
            case 'integer':
                $rules[] = 'integer';
                break;
            case 'boolean':
                $rules[] = 'boolean';
                break;
            case 'decimal':
                $rules[] = 'numeric';
                $rules[] = 'decimal:0,2';
                break;
            case 'timestamp':
            case 'date':
                $rules = ['nullable', 'date'];
                break;
            case 'foreignId':
                $tableName = Str::plural(str_replace('_id', '', $name));
                $rules[] = "exists:{$tableName},id";
                break;
            default:
                $rules[] = 'string';
                $rules[] = 'max:255';
        }

        if (in_array('unique', $modifiers)) {
            $table = "{{ table }}";
            $rules[] = "unique:{$table},{$name}";
        }

        return implode('|', $rules);
    }

    // File generation methods
    protected function generateModel(string $entity, string $module, bool $softDeletes): array
    {
        $common = $this->getCommonReplacements($entity, $module);
        $namespace = "{$common['rootNamespace']}\\Domain\\Models";
        
        $replacements = array_merge($common, [
            'namespace' => $namespace,
            'fillable' => $this->getFillableFields(),
            'casts' => $this->getCasts(),
            'useSoftDeletes' => $softDeletes ? "\nuse Illuminate\\Database\\Eloquent\\SoftDeletes;" : '',
            'softDeletesTrait' => $softDeletes ? ', SoftDeletes' : '',
        ]);

        $content = $this->getStubContent('model', $replacements);
        $path = app_path("Modules/{$module}/Domain/Models/{$entity}.php");
        
        $this->generateFile($path, $content, $this->option('force'));
        
        return ['type' => 'Model', 'path' => $path];
    }

    protected function generateRepositoryInterface(string $entity, string $module, bool $noPagination): array
    {
        $common = $this->getCommonReplacements($entity, $module);
        $namespace = "{$common['rootNamespace']}\\Domain\\Repositories";
        
        $replacements = array_merge($common, [
            'namespace' => $namespace,
            'paginationParam' => $noPagination ? '' : '?int $perPage = 15',
            'paginationComment' => $noPagination ? '' : ' with optional pagination',
        ]);

        $content = $this->getStubContent('repository-interface', $replacements);
        $path = app_path("Modules/{$module}/Domain/Repositories/{$entity}RepositoryInterface.php");
        
        $this->generateFile($path, $content, $this->option('force'));
        
        return ['type' => 'Repository Interface', 'path' => $path];
    }

    protected function generateRepository(string $entity, string $module, bool $noPagination): array
    {
        $common = $this->getCommonReplacements($entity, $module);
        $namespace = "{$common['rootNamespace']}\\Infrastructure\\Repositories";
        
        $paginationLogic = $noPagination 
            ? "return {$entity}::all();" 
            : "return \$perPage \n            ? {$entity}::paginate(\$perPage)\n            : {$entity}::all();";
        
        $replacements = array_merge($common, [
            'namespace' => $namespace,
            'modelNamespace' => "{$common['rootNamespace']}\\Domain\\Models",
            'repositoryInterfaceNamespace' => "{$common['rootNamespace']}\\Domain\\Repositories",
            'paginationParam' => $noPagination ? '' : '?int $perPage = 15',
            'paginationComment' => $noPagination ? '' : ' with optional pagination',
            'paginationLogic' => $paginationLogic,
        ]);

        $content = $this->getStubContent('repository', $replacements);
        $path = app_path("Modules/{$module}/Infrastructure/Repositories/{$entity}Repository.php");
        
        $this->generateFile($path, $content, $this->option('force'));
        
        return ['type' => 'Repository', 'path' => $path];
    }

    protected function generateResource(string $entity, string $module): array
    {
        $common = $this->getCommonReplacements($entity, $module);
        $namespace = "{$common['rootNamespace']}\\Infrastructure\\Resources";
        
        $replacements = array_merge($common, [
            'namespace' => $namespace,
            'resourceFields' => $this->getResourceFields(),
        ]);

        $content = $this->getStubContent('resource', $replacements);
        $path = app_path("Modules/{$module}/Infrastructure/Resources/{$entity}Resource.php");
        
        $this->generateFile($path, $content, $this->option('force'));
        
        return ['type' => 'Resource', 'path' => $path];
    }

    protected function generateService(string $entity, string $module, bool $noPagination): array
    {
        $common = $this->getCommonReplacements($entity, $module);
        $namespace = "{$common['rootNamespace']}\\Application\\Services";
        
        $replacements = array_merge($common, [
            'namespace' => $namespace,
            'repositoryInterfaceNamespace' => "{$common['rootNamespace']}\\Domain\\Repositories",
            'paginationParam' => $noPagination ? '' : '?int $perPage = 15',
            'paginationCall' => $noPagination ? '' : '$perPage',
            'paginationComment' => $noPagination ? '' : ' with optional pagination',
        ]);

        $content = $this->getStubContent('service', $replacements);
        $path = app_path("Modules/{$module}/Application/Services/{$entity}Service.php");
        
        $this->generateFile($path, $content, $this->option('force'));
        
        return ['type' => 'Service', 'path' => $path];
    }

    protected function generateStoreRequest(string $entity, string $module): array
    {
        $common = $this->getCommonReplacements($entity, $module);
        $namespace = "{$common['rootNamespace']}\\Application\\Requests";
        
        $rules = $this->getValidationRules(false);
        $rules = str_replace('{{ table }}', $common['table'], $rules);
        
        $replacements = array_merge($common, [
            'namespace' => $namespace,
            'validationRules' => $rules,
        ]);

        $content = $this->getStubContent('store-request', $replacements);
        $path = app_path("Modules/{$module}/Application/Requests/{$entity}StoreRequest.php");
        
        $this->generateFile($path, $content, $this->option('force'));
        
        return ['type' => 'Store Request', 'path' => $path];
    }

    protected function generateUpdateRequest(string $entity, string $module): array
    {
        $common = $this->getCommonReplacements($entity, $module);
        $namespace = "{$common['rootNamespace']}\\Application\\Requests";
        
        $rules = $this->getValidationRules(true);
        $rules = str_replace('{{ table }}', $common['table'], $rules);
        
        $replacements = array_merge($common, [
            'namespace' => $namespace,
            'validationRules' => $rules,
        ]);

        $content = $this->getStubContent('update-request', $replacements);
        $path = app_path("Modules/{$module}/Application/Requests/{$entity}UpdateRequest.php");
        
        $this->generateFile($path, $content, $this->option('force'));
        
        return ['type' => 'Update Request', 'path' => $path];
    }

    protected function generateController(string $entity, string $module, ?string $apiVersion, bool $noPagination): array
    {
        $common = $this->getCommonReplacements($entity, $module, $apiVersion);
        
        $controllerPath = $apiVersion 
            ? "Presentation/{$common['apiVersionNamespace']}/Controllers"
            : "Presentation/Controllers";
            
        $namespace = $apiVersion
            ? "{$common['rootNamespace']}\\Presentation\\{$common['apiVersionNamespace']}\\Controllers"
            : "{$common['rootNamespace']}\\Presentation\\Controllers";
        
        if ($noPagination) {
            $indexParam = '';
            $indexReturnType = ': JsonResponse';
            $indexBody = "\$data = \$this->service->getAll();\n        return {$entity}Resource::collection(\$data)\n            ->response();";
        } else {
            $indexParam = 'Request $request';
            $indexReturnType = ': JsonResponse';
            $indexBody = "\$perPage = \$request->input('per_page', 15);\n        \$data = \$this->service->getAll(\$perPage);\n        return {$entity}Resource::collection(\$data)\n            ->response();";
        }
        
        $replacements = array_merge($common, [
            'namespace' => $namespace,
            'serviceNamespace' => "{$common['rootNamespace']}\\Application\\Services",
            'resourceNamespace' => "{$common['rootNamespace']}\\Infrastructure\\Resources",
            'storeRequestNamespace' => "{$common['rootNamespace']}\\Application\\Requests",
            'updateRequestNamespace' => "{$common['rootNamespace']}\\Application\\Requests",
            'indexParam' => $indexParam,
            'indexReturnType' => $indexReturnType,
            'indexBody' => $indexBody,
        ]);

        $content = $this->getStubContent('controller', $replacements);
        $path = app_path("Modules/{$module}/{$controllerPath}/{$entity}Controller.php");
        
        $this->generateFile($path, $content, $this->option('force'));
        
        return ['type' => 'Controller', 'path' => $path];
    }

    protected function generateMigration(string $entity, bool $softDeletes): array
    {
        $common = $this->getCommonReplacements($entity, '');
        $timestamp = date('Y_m_d_His');
        
        $replacements = [
            'table' => $common['table'],
            'fields' => $this->getMigrationFields(),
            'softDeletes' => $softDeletes ? "\n            \$table->softDeletes();" : '',
        ];

        $content = $this->getStubContent('migration', $replacements);
        $path = database_path("migrations/{$timestamp}_create_{$common['table']}_table.php");
        
        $this->generateFile($path, $content, $this->option('force'));
        
        return ['type' => 'Migration', 'path' => $path];
    }

    protected function generateRouteFile(string $entity, string $module, ?string $apiVersion): array
    {
        $common = $this->getCommonReplacements($entity, $module, $apiVersion);
        
        $routeFileName = $apiVersion ? "api_{$apiVersion}.php" : "api.php";
        $routePrefix = $apiVersion 
            ? "{$apiVersion}/{$common['moduleNameLower']}"
            : $common['moduleNameLower'];
            
        $controllerNamespace = $apiVersion
            ? "{$common['rootNamespace']}\\Presentation\\{$common['apiVersionNamespace']}\\Controllers"
            : "{$common['rootNamespace']}\\Presentation\\Controllers";
        
        $replacements = array_merge($common, [
            'controllerNamespace' => $controllerNamespace,
            'routePrefix' => $routePrefix,
        ]);

        $path = app_path("Modules/{$module}/routes/{$routeFileName}");
        
        // If route file doesn't exist, create it with full stub content
        if (!File::exists($path)) {
            $content = $this->getStubContent('module-routes', $replacements);
            $this->generateFile($path, $content, false);
        } else {
            // Route file exists, append new route
            $useStatement = "use {$controllerNamespace}\\{$entity}Controller;";
            $routeStatement = "Route::prefix('{$routePrefix}')->group(function () {\n    Route::apiResource('{$common['pluralVariable']}', {$entity}Controller::class);\n});";
            
            $existingContent = File::get($path);
            
            // Check if this controller is already imported
            if (!Str::contains($existingContent, $useStatement)) {
                // Find the last use statement and add after it
                if (preg_match('/^use .+;$/m', $existingContent, $matches, PREG_OFFSET_CAPTURE)) {
                    $lastUsePosition = $matches[0][1] + strlen($matches[0][0]);
                    $existingContent = substr_replace($existingContent, "\n{$useStatement}", $lastUsePosition, 0);
                } else {
                    // No use statements found, add after <?php
                    $existingContent = str_replace("<?php\n", "<?php\n\n{$useStatement}\n", $existingContent);
                }
            }
            
            // Append route at the end of the file
            if (!Str::contains($existingContent, "{$entity}Controller::class")) {
                $existingContent = rtrim($existingContent) . "\n\n{$routeStatement}\n";
            }
            
            File::put($path, $existingContent);
        }
        
        return ['type' => 'Route file', 'path' => $path];
    }

    /**
     * Get casts for model
     */
    protected function getCasts(): string
    {
        if (empty($this->fields)) {
            return '';
        }

        $casts = [];
        foreach ($this->fields as $field) {
            if ($field['type'] === 'boolean') {
                $casts[] = "'{$field['name']}' => 'boolean'";
            } elseif ($field['type'] === 'decimal') {
                $casts[] = "'{$field['name']}' => 'decimal:2'";
            } elseif (in_array($field['type'], ['timestamp', 'date'])) {
                $casts[] = "'{$field['name']}' => 'datetime'";
            }
        }

        return !empty($casts) ? implode(",\n        ", $casts) : '';
    }

    /**
     * Get resource fields
     */
    protected function getResourceFields(): string
    {
        if (empty($this->fields)) {
            return "'name' => \$this->name,";
        }

        $fields = [];
        foreach ($this->fields as $field) {
            $fields[] = "'{$field['name']}' => \$this->{$field['name']},";
        }

        return implode("\n            ", $fields);
    }
}
