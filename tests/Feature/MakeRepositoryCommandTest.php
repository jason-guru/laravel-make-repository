<?php

declare(strict_types=1);

namespace JasonGuru\LaravelMakeRepository\Tests\Feature;

use Illuminate\Support\Facades\File;
use JasonGuru\LaravelMakeRepository\Tests\TestCase;

class MakeRepositoryCommandTest extends TestCase
{
    private string $generatedPath;

    private string $generatedInterfacePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generatedPath = app_path('Repositories/UserRepository.php');
        $this->generatedInterfacePath = app_path('Repositories/Contracts/UserRepositoryInterface.php');

        $this->cleanupGeneratedFiles();
    }

    protected function tearDown(): void
    {
        $this->cleanupGeneratedFiles();

        parent::tearDown();
    }

    private function cleanupGeneratedFiles(): void
    {
        foreach ([$this->generatedPath, $this->generatedInterfacePath] as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }

        $contractsDir = dirname($this->generatedInterfacePath);
        if (File::isDirectory($contractsDir) && count(File::files($contractsDir)) === 0) {
            File::deleteDirectory($contractsDir);
        }

        $repoDir = dirname($this->generatedPath);
        if (File::isDirectory($repoDir) && count(File::files($repoDir)) === 0 && count(File::directories($repoDir)) === 0) {
            File::deleteDirectory($repoDir);
        }
    }

    public function testCommandIsRegistered(): void
    {
        $this->assertArrayHasKey('make:repository', $this->app['Illuminate\Contracts\Console\Kernel']->all());
    }

    public function testItGeneratesARepositoryClass(): void
    {
        $this->artisan('make:repository', ['name' => 'UserRepository', '--no-interface' => true])
            ->assertSuccessful();

        $this->assertFileExists($this->generatedPath);

        $contents = File::get($this->generatedPath);

        $this->assertStringContainsString('namespace App\Repositories;', $contents);
        $this->assertStringContainsString('class UserRepository extends BaseRepository', $contents);
        $this->assertStringContainsString('use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;', $contents);
        $this->assertStringContainsString('public function model(): string', $contents);
        $this->assertStringContainsString('//use Your Model', $contents);
        $this->assertStringContainsString('//return YourModel::class;', $contents);
    }

    public function testItWiresUpTheModelOption(): void
    {
        $this->artisan('make:repository', ['name' => 'UserRepository', '--model' => 'User', '--no-interface' => true])
            ->assertSuccessful();

        $contents = File::get($this->generatedPath);

        $this->assertStringContainsString('use App\Models\User;', $contents);
        $this->assertStringContainsString('return User::class;', $contents);
        $this->assertStringNotContainsString('//use Your Model', $contents);
        $this->assertStringNotContainsString('//return YourModel::class;', $contents);
    }

    public function testTheModelOptionAcceptsFullyQualifiedNames(): void
    {
        $this->artisan('make:repository', ['name' => 'UserRepository', '--model' => '\App\Domain\Blog\Post', '--no-interface' => true])
            ->assertSuccessful();

        $contents = File::get($this->generatedPath);

        $this->assertStringContainsString('use App\Domain\Blog\Post;', $contents);
        $this->assertStringContainsString('return Post::class;', $contents);
    }

    public function testItReportsWhenRepositoryAlreadyExists(): void
    {
        $this->artisan('make:repository', ['name' => 'UserRepository', '--no-interface' => true])
            ->assertSuccessful();

        $this->artisan('make:repository', ['name' => 'UserRepository', '--no-interface' => true])
            ->expectsOutputToContain('Repository already exists');
    }

    public function testItGeneratesAPairedInterfaceByDefault(): void
    {
        $this->artisan('make:repository', ['name' => 'UserRepository'])
            ->assertSuccessful();

        $this->assertFileExists($this->generatedPath);
        $this->assertFileExists($this->generatedInterfacePath);

        $interfaceContents = File::get($this->generatedInterfacePath);
        $this->assertStringContainsString('namespace App\Repositories\Contracts;', $interfaceContents);
        $this->assertStringContainsString('interface UserRepositoryInterface extends RepositoryContract', $interfaceContents);
        $this->assertStringContainsString('use JasonGuru\LaravelMakeRepository\Repository\RepositoryContract;', $interfaceContents);

        $concreteContents = File::get($this->generatedPath);
        $this->assertStringContainsString('use App\Repositories\Contracts\UserRepositoryInterface;', $concreteContents);
        $this->assertStringContainsString('class UserRepository extends BaseRepository implements UserRepositoryInterface', $concreteContents);
    }

    public function testTheNoInterfaceFlagSkipsInterfaceGeneration(): void
    {
        $this->artisan('make:repository', ['name' => 'UserRepository', '--no-interface' => true])
            ->assertSuccessful();

        $this->assertFileExists($this->generatedPath);
        $this->assertFileDoesNotExist($this->generatedInterfacePath);

        $contents = File::get($this->generatedPath);
        $this->assertStringNotContainsString('implements', $contents);
        $this->assertStringNotContainsString('UserRepositoryInterface', $contents);
    }

    public function testWithInterfaceConfigCanBeDisabled(): void
    {
        $this->app['config']->set('repository.with_interface', false);

        $this->artisan('make:repository', ['name' => 'UserRepository'])
            ->assertSuccessful();

        $this->assertFileExists($this->generatedPath);
        $this->assertFileDoesNotExist($this->generatedInterfacePath);
    }
}
