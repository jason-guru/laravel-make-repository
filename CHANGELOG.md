# Changelog

All notable changes to `jason-guru/laravel-make-repository` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-06-01

### Added

- Paired interface generation. `php artisan make:repository UserRepository` now also creates `App\Repositories\Contracts\UserRepositoryInterface` (extending `RepositoryContract`) and the concrete class is declared `implements UserRepositoryInterface`. Pass `--no-interface` to skip.
- Publishable config file at `config/repository.php` exposing `path`, `namespace`, `with_interface`, and `bind`. Publish with `php artisan vendor:publish --tag=repository-config`.
- Auto-binding. When `repository.bind` is `true` (default), the service provider scans the configured `Contracts` directory on boot and binds each `*Interface` to its matching concrete class, so type-hinting the interface anywhere (controllers, jobs, etc.) resolves the repository.
- `--model` (`-m`) option on `make:repository`. Example: `php artisan make:repository UserRepository --model=User` generates a repository with `use App\Models\User;` and `return User::class;` already wired up.
- Support for Laravel 10, 11, 12, and 13.
- PHP 8.1+ type declarations across all source files (typed properties, parameter and return types, `static` return types, `int|string` IDs, `mixed`).
- `declare(strict_types=1)` in every PHP file.
- `orchestra/testbench` test suite with 22 tests / 42 assertions covering the `make:repository` command, stub generation, duplicate detection, and `BaseRepository` CRUD, query chain (`where`, `whereIn`, `orderBy`, `limit`), pagination, eager loading, and error paths.
- `phpunit.xml.dist` with in-memory SQLite database for fast, isolated test runs.
- `autoload-dev` PSR-4 mapping for `tests/`.
- `.gitignore` for `vendor/`, `composer.lock`, and the phpunit cache.
- `composer test` script alias for `vendor/bin/phpunit`.

### Changed

- **BREAKING**: Minimum PHP version is now 8.1 (was 5.4).
- **BREAKING**: Minimum Laravel version is now 10.x.
- **BREAKING**: `BaseRepository::model()` is now declared `abstract public function model(): string` — subclasses must declare the `: string` return type.
- **BREAKING**: Removed `GeneralException::render()`, which depended on a non-existent `withFlashDanger()` helper. Implement rendering in your application's exception handler if needed.
- Renamed `src/repository/` → `src/Repository/` and `src/exceptions/` → `src/Exceptions/` so PSR-4 autoloading works on case-sensitive filesystems (Linux CI).
- Switched to a pure PSR-4 autoload — the legacy `classmap` entry is gone.
- `RepositoryServiceProvider` now only registers the command when running in the console.
- The generated `make:repository` stub now declares `model(): string` to match the abstract signature.

### Fixed

- `BaseRepository::unsetClauses()` now also resets `orderBys`, so an ordering clause from a previous call no longer leaks into the next query.
- Removed the broken `MakeRepository::alreadyExists()` override — it called `class_exists()` on the unqualified raw name (e.g. `"UserRepository"`), which never matched. Laravel's default file-existence check is correct and is now used.

### Removed

- `classmap` autoload entry from `composer.json`.
- `GeneralException::render()` method.
- `MakeRepository::alreadyExists()` override.

## [0.0.3] - earlier

Previous releases targeted Laravel 5 and above on PHP 5.4+. See git history for details.
