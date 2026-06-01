<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Repository output location
    |--------------------------------------------------------------------------
    |
    | The filesystem path and PHP namespace where `make:repository` writes
    | generated classes. Keep these aligned with your composer.json PSR-4
    | autoload map.
    |
    */

    'path' => 'app/Repositories',

    'namespace' => 'App\\Repositories',

    /*
    |--------------------------------------------------------------------------
    | Generate a paired interface
    |--------------------------------------------------------------------------
    |
    | When true, `make:repository UserRepository` also generates
    | `Contracts/UserRepositoryInterface.php` and the concrete class is
    | declared to implement it. Pass `--no-interface` on the command to skip.
    |
    */

    'with_interface' => true,

    /*
    |--------------------------------------------------------------------------
    | Auto-bind interfaces to concretes
    |--------------------------------------------------------------------------
    |
    | When true, the service provider scans the configured Contracts
    | directory at boot and binds each `*Interface` to its matching concrete
    | class in the container.
    |
    */

    'bind' => true,
];
