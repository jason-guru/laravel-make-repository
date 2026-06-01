<?php

declare(strict_types=1);

namespace JasonGuru\LaravelMakeRepository\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    protected $guarded = [];
}
