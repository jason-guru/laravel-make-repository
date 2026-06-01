<?php

declare(strict_types=1);

namespace JasonGuru\LaravelMakeRepository\Tests\Feature;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JasonGuru\LaravelMakeRepository\Exceptions\GeneralException;
use JasonGuru\LaravelMakeRepository\Tests\Fixtures\InvalidRepository;
use JasonGuru\LaravelMakeRepository\Tests\Fixtures\User;
use JasonGuru\LaravelMakeRepository\Tests\Fixtures\UserRepository;
use JasonGuru\LaravelMakeRepository\Tests\TestCase;

class BaseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new UserRepository();
    }

    private function seedUsers(int $count = 3): Collection
    {
        $users = new Collection();
        for ($i = 1; $i <= $count; $i++) {
            $users->push(User::create([
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'active' => $i % 2 === 1,
            ]));
        }
        return $users;
    }

    public function testMakeModelThrowsWhenClassIsNotAModel(): void
    {
        $this->expectException(GeneralException::class);
        new InvalidRepository();
    }

    public function testCreatePersistsAModel(): void
    {
        $user = $this->repo->create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    }

    public function testCreateMultiplePersistsEachRecord(): void
    {
        $models = $this->repo->createMultiple([
            ['name' => 'A', 'email' => 'a@example.com'],
            ['name' => 'B', 'email' => 'b@example.com'],
        ]);

        $this->assertCount(2, $models);
        $this->assertDatabaseCount('users', 2);
    }

    public function testAllReturnsEveryRecord(): void
    {
        $this->seedUsers(3);

        $all = $this->repo->all();

        $this->assertCount(3, $all);
    }

    public function testCountReturnsTotalRecords(): void
    {
        $this->seedUsers(4);

        $this->assertSame(4, $this->repo->count());
    }

    public function testGetByIdReturnsTheRecord(): void
    {
        $users = $this->seedUsers(2);

        $found = $this->repo->getById($users[1]->id);

        $this->assertTrue($found->is($users[1]));
    }

    public function testGetByIdThrowsWhenMissing(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repo->getById(999);
    }

    public function testGetByColumnReturnsFirstMatch(): void
    {
        $this->seedUsers(2);

        $found = $this->repo->getByColumn('user2@example.com', 'email');

        $this->assertNotNull($found);
        $this->assertSame('User 2', $found->name);
    }

    public function testGetByColumnReturnsNullWhenMissing(): void
    {
        $this->assertNull($this->repo->getByColumn('missing@example.com', 'email'));
    }

    public function testUpdateByIdUpdatesTheRecord(): void
    {
        $users = $this->seedUsers(1);

        $updated = $this->repo->updateById($users[0]->id, ['name' => 'Renamed']);

        $this->assertSame('Renamed', $updated->name);
        $this->assertDatabaseHas('users', ['id' => $users[0]->id, 'name' => 'Renamed']);
    }

    public function testDeleteByIdRemovesTheRecord(): void
    {
        $users = $this->seedUsers(2);

        $this->repo->deleteById($users[0]->id);

        $this->assertDatabaseMissing('users', ['id' => $users[0]->id]);
        $this->assertDatabaseCount('users', 1);
    }

    public function testDeleteMultipleByIdRemovesAllListed(): void
    {
        $users = $this->seedUsers(3);

        $count = $this->repo->deleteMultipleById([$users[0]->id, $users[1]->id]);

        $this->assertSame(2, $count);
        $this->assertDatabaseCount('users', 1);
    }

    public function testWhereFiltersResults(): void
    {
        $this->seedUsers(3);

        $results = $this->repo->where('active', true)->get();

        $this->assertCount(2, $results);
        foreach ($results as $r) {
            $this->assertTrue((bool) $r->active);
        }
    }

    public function testWhereInFiltersResults(): void
    {
        $users = $this->seedUsers(3);

        $results = $this->repo
            ->whereIn('id', [$users[0]->id, $users[2]->id])
            ->get();

        $this->assertCount(2, $results);
    }

    public function testOrderByAndLimitChain(): void
    {
        $this->seedUsers(3);

        $results = $this->repo
            ->orderBy('id', 'desc')
            ->limit(2)
            ->get();

        $this->assertCount(2, $results);
        $this->assertSame('User 3', $results[0]->name);
        $this->assertSame('User 2', $results[1]->name);
    }

    public function testClausesResetBetweenCalls(): void
    {
        $this->seedUsers(3);

        $this->repo->where('active', true)->get();

        $this->assertCount(3, $this->repo->all());
        $this->assertCount(3, $this->repo->get());
    }

    public function testFirstThrowsWhenNoRecordMatches(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repo->where('email', 'nope@example.com')->first();
    }

    public function testPaginateReturnsLengthAwarePaginator(): void
    {
        $this->seedUsers(5);

        $page = $this->repo->paginate(2);

        $this->assertSame(2, $page->count());
        $this->assertSame(5, $page->total());
        $this->assertSame(3, $page->lastPage());
    }

    public function testDeleteWithWhereRemovesMatchingRows(): void
    {
        $this->seedUsers(3);

        $deleted = $this->repo->where('active', false)->delete();

        $this->assertSame(1, $deleted);
        $this->assertDatabaseCount('users', 2);
    }
}
