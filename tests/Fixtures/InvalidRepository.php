<?php

declare(strict_types=1);

namespace JasonGuru\LaravelMakeRepository\Tests\Fixtures;

use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;

class InvalidRepository extends BaseRepository
{
    public function model(): string
    {
        return NotAModel::class;
    }
}
