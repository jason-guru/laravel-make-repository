# Laravel PHP Artisan Make:Repository

[![Latest Stable Version](https://poser.pugx.org/jason-guru/laravel-make-repository/version)](https://packagist.org/packages/jason-guru/laravel-make-repository)
[![Total Downloads](https://poser.pugx.org/jason-guru/laravel-make-repository/downloads)](https://packagist.org/packages/jason-guru/laravel-make-repository)
[![Latest Unstable Version](https://poser.pugx.org/jason-guru/laravel-make-repository/v/unstable)](https://packagist.org/packages/jason-guru/laravel-make-repository)
[![License](https://poser.pugx.org/jason-guru/laravel-make-repository/license)](https://packagist.org/packages/jason-guru/laravel-make-repository)

A simple package that adds a `php artisan make:repository` command to Laravel 10, 11, 12, and 13.

## Installation

Require the package with composer:

```bash
composer require jason-guru/laravel-make-repository --dev
```

Or add it to your `composer.json` `require-dev` section and run `composer update`:

```json
"require-dev": {
    "jason-guru/laravel-make-repository": "^0.0.3"
}
```

## Usage

```bash
php artisan make:repository your-repository-name
```

Example:

```bash
php artisan make:repository UserRepository
```

Or with a sub-namespace:

```bash
php artisan make:repository Backend\\UserRepository
```

Wire up a model in one shot with the `--model` (`-m`) option — the generated repository imports the model and returns it from `model()`:

```bash
php artisan make:repository UserRepository --model=User
```

The above commands create a `Repositories` directory inside the `app` directory.

## Generated files

By default, `make:repository UserRepository` creates **two** files:

- `app/Repositories/UserRepository.php` — the concrete class
- `app/Repositories/Contracts/UserRepositoryInterface.php` — the paired interface (extends `RepositoryContract`)

The concrete is declared `implements UserRepositoryInterface`, and the package's service provider auto-binds the interface to the concrete in the container — so you can type-hint the interface anywhere:

```php
public function __construct(private UserRepositoryInterface $users) {}
```

To skip the interface for a single command, pass `--no-interface`:

```bash
php artisan make:repository UserRepository --no-interface
```

## Configuration

Publish the config to customize behavior:

```bash
php artisan vendor:publish --tag=repository-config
```

That creates `config/repository.php`:

```php
return [
    'path'           => 'app/Repositories',
    'namespace'      => 'App\\Repositories',
    'with_interface' => true,  // generate a paired interface
    'bind'           => true,  // auto-bind interface => concrete
];
```

## Example output

A repository generated with `--model=User` looks like this:

```php
<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function model(): string
    {
        return User::class;
    }
}
```
