<?php

namespace Hexaora\CrudGenerator;

use Illuminate\Support\ServiceProvider;
use Hexaora\CrudGenerator\Console\Commands\MakeHexaoraCommand;

class HexaoraServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Register commands
            $this->commands([
                MakeHexaoraCommand::class,
            ]);

            // Publish stubs
            $this->publishes([
                __DIR__.'/../stubs/hexaora' => base_path('stubs/hexaora'),
            ], 'hexaora-stubs');

            // Publish config (optional for future use)
            $this->publishes([
                __DIR__.'/../config/hexaora.php' => config_path('hexaora.php'),
            ], 'hexaora-config');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Merge config (optional for future use)
        $this->mergeConfigFrom(
            __DIR__.'/../config/hexaora.php',
            'hexaora'
        );
    }
}
