<?php

declare(strict_types=1);

namespace Chronos\EventCore\Tests\Unit\Repositories;

use Chronos\EventCore\Models\Event;
use Chronos\EventCore\Repositories\EventRepository;
use Chronos\EventCore\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class EventRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected EventRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EventRepository();
    }

    public function test_it_returns_only_events_belonging_to_user(): void
    {
        // 1. Создаем двух пользователей
        $userA = User::factory()->create(['id' => 1]);
        $userB = User::factory()->create(['id' => 2]);

        // 2. Создаем события для каждого
        Event::factory()->create(['user_id' => $userA->id, 'title' => 'User A Event']);
        Event::factory()->create(['user_id' => $userB->id, 'title' => 'User B Event']);

        // 3. Выполняем запрос через репозиторий
        $results = $this->repository->getForUser($userA->id);

        // 4. Проверяем
        $this->assertCount(1, $results);
        $this->assertEquals('User A Event', $results->first()->title);
    }
}