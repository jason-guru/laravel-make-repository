<?php

declare(strict_types=1);

namespace JasonGuru\LaravelMakeRepository\Tests\Feature;

use Illuminate\Support\Facades\File;
use JasonGuru\LaravelMakeRepository\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    private string $generatedRepoPath;

    private string $generatedInterfacePath;

    private string $publishedConfigPath;

    protected bool $disableBindForNextRefresh = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generatedRepoPath = app_path('Repositories/BoundRepository.php');
        $this->generatedInterfacePath = app_path('Repositories/Contracts/BoundRepositoryInterface.php');
        $this->publishedConfigPath = config_path('repository.php');

        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        if ($this->disableBindForNextRefresh) {
            $app['config']->set('repository.bind', false);
        }
    }

    private function cleanup(): void
    {
        foreach ([$this->generatedRepoPath, $this->generatedInterfacePath, $this->publishedConfigPath] as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }

        foreach ([dirname($this->generatedInterfacePath), dirname($this->generatedRepoPath)] as $dir) {
            if (File::isDirectory($dir) && count(File::files($dir)) === 0 && count(File::directories($dir)) === 0) {
                File::deleteDirectory($dir);
            }
        }
    }

    public function testConfigIsMergedWithDefaults(): void
    {
        $this->assertSame('app/Repositories', $this->app['config']->get('repository.path'));
        $this->assertSame('App\\Repositories', $this->app['config']->get('repository.namespace'));
        $this->assertTrue($this->app['config']->get('repository.with_interface'));
        $this->assertTrue($this->app['config']->get('repository.bind'));
    }

    public function testConfigCanBePublished(): void
    {
        $this->artisan('vendor:publish', ['--tag' => 'repository-config'])
            ->assertSuccessful();

        $this->assertFileExists($this->publishedConfigPath);

        $contents = File::get($this->publishedConfigPath);
        $this->assertStringContainsString("'path' => 'app/Repositories'", $contents);
        $this->assertStringContainsString("'namespace' => 'App\\\\Repositories'", $contents);
        $this->assertStringContainsString("'with_interface' => true", $contents);
        $this->assertStringContainsString("'bind' => true", $contents);
    }

    public function testAutoBindBindsInterfaceToConcreteWhenEnabled(): void
    {
        $this->artisan('make:repository', ['name' => 'BoundRepository', '--model' => 'User'])
            ->assertSuccessful();

        $this->refreshApplication();

        $this->assertTrue(
            $this->app->bound(\App\Repositories\Contracts\BoundRepositoryInterface::class),
            'Expected the auto-bind to register the interface in the container.',
        );

        $this->app->instance('App\Models\User', new \JasonGuru\LaravelMakeRepository\Tests\Fixtures\User());

        $resolved = $this->app->make(\App\Repositories\Contracts\BoundRepositoryInterface::class);
        $this->assertInstanceOf(\App\Repositories\BoundRepository::class, $resolved);
    }

    public function testAutoBindIsSkippedWhenDisabled(): void
    {
        $this->artisan('make:repository', ['name' => 'BoundRepository'])
            ->assertSuccessful();

        $this->disableBindForNextRefresh = true;
        $this->refreshApplication();

        $this->assertFalse(
            $this->app->bound(\App\Repositories\Contracts\BoundRepositoryInterface::class),
        );
    }
}
