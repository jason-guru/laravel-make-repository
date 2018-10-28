# laravel-make-repository
A simple package to add `php artisan make:repository` command to Laravel 5+

##Installation
Require the package with composer using the following command:

`composer require jason-guru/laravel-make-repository`

Or add the following to your composer.json's require section and `composer update`

```json
"require-dev": {
          "jason-guru/laravel-make-repository": "dev-master"
}
```
##Usage
`php artisan make:repository your-repository-name`

Example:
```
php artisan make:repository UserRepository
```
or
```
php artisan make:repository Backend\UserRepository
```

The above will create a repositories directory inside the app directory.

Once the repository is generated add your model class and return it in the model function,

Example:

```
<?php

namespace App\Repositories;

use JasonGuru\RepositoryGenerator\Repository\BaseRepository;
use App\Models\User;

class UserRepository extends BaseRepository
{
    /**
     * @return string
     *  Return the model
     */
    public function model()
    {
        return User::class;
    }
}

```


