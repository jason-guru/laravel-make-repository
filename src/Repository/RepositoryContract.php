<?php

declare(strict_types=1);

namespace JasonGuru\LaravelMakeRepository\Repository;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryContract
{
    public function all(array $columns = ['*']): Collection;

    public function count(): int;

    public function create(array $data): Model;

    public function createMultiple(array $data): Collection;

    public function delete(): mixed;

    public function deleteById(int|string $id): ?bool;

    public function deleteMultipleById(array $ids): int;

    public function first(array $columns = ['*']): Model;

    public function get(array $columns = ['*']): Collection;

    public function getById(int|string $id, array $columns = ['*']): Model;

    public function getByColumn(mixed $item, string $column, array $columns = ['*']): ?Model;

    public function paginate(int $limit = 25, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator;

    public function updateById(int|string $id, array $data, array $options = []): Model;

    public function limit(int $limit): static;

    public function orderBy(string $column, string $direction = 'asc'): static;

    public function where(string $column, mixed $value, string $operator = '='): static;

    public function whereIn(string $column, mixed $values): static;

    public function with(array|string $relations): static;
}
