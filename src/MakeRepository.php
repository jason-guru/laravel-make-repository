<?php

declare(strict_types=1);

namespace JasonGuru\LaravelMakeRepository;

use Illuminate\Console\GeneratorCommand;

class MakeRepository extends GeneratorCommand
{
    protected $signature = 'make:repository {name : The repository class name}
                            {--m|model= : The model class the repository wraps}
                            {--no-interface : Do not generate a paired interface}';

    protected $description = 'Create a new repository class';

    protected $type = 'Repository';

    protected function getStub(): string
    {
        return __DIR__.'/stubs/repository.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        $configured = $this->laravel['config']->get('repository.namespace');

        if (is_string($configured) && $configured !== '') {
            return trim($configured, '\\');
        }

        return $rootNamespace.'\\Repositories';
    }

    public function handle(): bool|null
    {
        $result = parent::handle();

        if ($result === false) {
            return false;
        }

        if ($this->shouldGenerateInterface()) {
            $this->generateInterface();
        }

        return $result;
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);
        $stub = $this->replaceModel($stub);

        return $this->replaceInterface($stub, $name);
    }

    protected function replaceModel(string $stub): string
    {
        $model = $this->option('model');

        if ($model === null || $model === '') {
            return str_replace(
                ['{{ modelImport }}', '{{ modelReturn }}'],
                ['//use Your Model', '//return YourModel::class;'],
                $stub,
            );
        }

        $modelClass = $this->qualifyModel($model);
        $shortName = class_basename($modelClass);

        return str_replace(
            ['{{ modelImport }}', '{{ modelReturn }}'],
            ["use {$modelClass};", "return {$shortName}::class;"],
            $stub,
        );
    }

    protected function replaceInterface(string $stub, string $name): string
    {
        if (! $this->shouldGenerateInterface()) {
            return str_replace(
                ['{{ interfaceImport }}', '{{ implementsClause }}'],
                ['', ''],
                $stub,
            );
        }

        $shortName = class_basename($name).'Interface';
        $interfaceFqcn = $this->interfaceNamespace().'\\'.$shortName;

        return str_replace(
            ['{{ interfaceImport }}', '{{ implementsClause }}'],
            ["\nuse {$interfaceFqcn};", " implements {$shortName}"],
            $stub,
        );
    }

    protected function shouldGenerateInterface(): bool
    {
        if ($this->option('no-interface')) {
            return false;
        }

        return (bool) $this->laravel['config']->get('repository.with_interface', false);
    }

    protected function interfaceNamespace(): string
    {
        return $this->getDefaultNamespace($this->rootNamespace()).'\\Contracts';
    }

    protected function generateInterface(): void
    {
        $concreteName = $this->qualifyClass($this->getNameInput());
        $shortName = class_basename($concreteName).'Interface';
        $namespace = $this->interfaceNamespace();
        $qualifiedInterface = $namespace.'\\'.$shortName;
        $path = $this->getPath($qualifiedInterface);

        if ($this->files->exists($path)) {
            $this->components->warn(sprintf('Interface [%s] already exists; skipped.', $path));

            return;
        }

        $this->makeDirectory($path);

        $stub = $this->files->get(__DIR__.'/stubs/repository-interface.stub');
        $stub = str_replace(
            ['DummyNamespace', 'DummyClass'],
            [$namespace, $shortName],
            $stub,
        );

        $this->files->put($path, $stub);
        $this->components->info(sprintf('Interface [%s] created successfully.', $path));
    }
}
