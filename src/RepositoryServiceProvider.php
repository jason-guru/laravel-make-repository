<?php

declare(strict_types=1);

namespace JasonGuru\LaravelMakeRepository;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/repository.php', 'repository');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeRepository::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/repository.php' => $this->app['path.config'].'/repository.php',
        ], 'repository-config');

        if ($this->app['config']->get('repository.bind')) {
            $this->autoBindRepositories();
        }
    }

    protected function autoBindRepositories(): void
    {
        $namespace = trim((string) $this->app['config']->get('repository.namespace'), '\\');
        $path = (string) $this->app['config']->get('repository.path');

        if ($namespace === '' || $path === '') {
            return;
        }

        $contractsDir = $this->app->basePath($path).'/Contracts';

        if (! is_dir($contractsDir)) {
            return;
        }

        foreach (glob($contractsDir.'/*Interface.php') ?: [] as $interfaceFile) {
            $shortName = basename($interfaceFile, '.php');
            $concretePath = dirname($contractsDir).'/'.substr($shortName, 0, -strlen('Interface')).'.php';

            if (! is_file($concretePath)) {
                continue;
            }

            $interface = $namespace.'\\Contracts\\'.$shortName;
            $concrete = $namespace.'\\'.substr($shortName, 0, -strlen('Interface'));

            if (! interface_exists($interface)) {
                require_once $interfaceFile;
            }

            if (! class_exists($concrete)) {
                require_once $concretePath;
            }

            if (interface_exists($interface) && class_exists($concrete)) {
                $this->app->bind($interface, $concrete);
            }
        }
    }
}
