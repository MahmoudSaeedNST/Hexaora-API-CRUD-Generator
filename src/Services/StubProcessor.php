<?php

namespace Hexaora\CrudGenerator\Services;

use Illuminate\Support\Facades\File;

class StubProcessor
{
    /**
     * Get stub content and replace placeholders.
     */
    public function getStubContent(string $stubName, array $replacements): string
    {
        $stubPath = $this->getStubPath($stubName);

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
     * Get the path to a stub file.
     */
    protected function getStubPath(string $stubName): string
    {
        // First check published stubs
        $publishedPath = base_path("stubs/hexaora/{$stubName}.stub");
        if (File::exists($publishedPath)) {
            return $publishedPath;
        }

        // Fall back to package stubs
        return __DIR__ . "/../../stubs/hexaora/{$stubName}.stub";
    }
}
