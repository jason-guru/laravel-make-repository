<?php

declare(strict_types=1);

namespace JasonGuru\LaravelMakeRepository\Tests\Fixtures;

use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;

class UserRepository extends BaseRepository
{
    public function model(): string
    {
        return User::class;
    }
}
