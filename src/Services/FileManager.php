<?php

namespace Hexaora\CrudGenerator\Services;

use Illuminate\Support\Facades\File;

class FileManager
{
    /**
     * Generate a file from content.
     */
    public function generateFile(string $path, string $content, bool $force = false): void
    {
        $directory = dirname($path);

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (File::exists($path) && !$force) {
            throw new \Exception("File already exists: {$path}. Use --force to overwrite.");
        }

        File::put($path, $content);
    }

    /**
     * Append content to a file.
     */
    public function appendToFile(string $path, string $content): void
    {
        if (!File::exists($path)) {
            throw new \Exception("File not found: {$path}");
        }

        File::append($path, $content);
    }

    /**
     * Check if content exists in file.
     */
    public function fileContains(string $path, string $search): bool
    {
        if (!File::exists($path)) {
            return false;
        }

        return str_contains(File::get($path), $search);
    }

    /**
     * Replace content in file.
     */
    public function replaceInFile(string $path, string $search, string $replace): void
    {
        if (!File::exists($path)) {
            throw new \Exception("File not found: {$path}");
        }

        $content = File::get($path);
        $content = str_replace($search, $replace, $content);
        File::put($path, $content);
    }
}
