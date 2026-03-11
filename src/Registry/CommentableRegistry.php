<?php

namespace BlackpigCreatif\Replique\Registry;

use BlackpigCreatif\Replique\Attributes\Commentable;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

class CommentableRegistry
{
    /** @var array<class-string, string> */
    private array $models = [];

    public function boot(): void
    {
        try {
            $this->models = Cache::rememberForever(
                'replique.commentable_models',
                fn (): array => $this->discover(),
            );
        } catch (\Throwable) {
            // Cache unavailable (e.g. database driver with no cache table during
            // package:discover or test bootstrap) — fall back to direct discovery.
            $this->models = $this->discover();
        }

        // Merge manual config entries (config takes label precedence)
        foreach (config('replique.commentable_models', []) as $class => $label) {
            $this->models[$class] = $label;
        }
    }

    /**
     * @return array<class-string, string>
     */
    public function all(): array
    {
        return $this->models;
    }

    public function register(string $class, string $label): void
    {
        $this->models[$class] = $label;
    }

    public function forget(): void
    {
        Cache::forget('replique.commentable_models');
        $this->models = [];
    }

    /**
     * @return array<class-string, string>
     */
    private function discover(): array
    {
        $discovered = [];

        if (! function_exists('app_path')) {
            return $discovered;
        }

        $appPath = app_path();

        if (! is_dir($appPath)) {
            return $discovered;
        }

        $classMap = $this->buildClassMap($appPath);

        foreach ($classMap as $class) {
            if (! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(Commentable::class);

            if (empty($attributes)) {
                continue;
            }

            /** @var Commentable $attribute */
            $attribute = $attributes[0]->newInstance();
            $discovered[$class] = $attribute->label ?? class_basename($class);
        }

        return $discovered;
    }

    /**
     * @return array<string>
     */
    private function buildClassMap(string $path): array
    {
        $classes = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            if (preg_match('/^namespace\s+(.+?);/m', $content, $nsMatch) &&
                preg_match('/^class\s+(\w+)/m', $content, $classMatch)) {
                $classes[] = $nsMatch[1] . '\\' . $classMatch[1];
            }
        }

        return $classes;
    }
}
