<?php

declare(strict_types=1);

namespace Chronos\EventCore\Tests\Unit\Repositories;

use Chronos\EventCore\Models\Event;
use Chronos\EventCore\Repositories\EventInstanceRepository;
use Chronos\EventCore\Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;

/**
 * Class EventInstanceRepositoryTest
 */
class EventInstanceRepositoryTest extends TestCase
{
    /**
     * Проверка метода updateOrCreateOverride на предотвращение дублей.
     */
    public function test_it_upserts_instance_correctly(): void
    {
        // СОЗДАЕМ ПОЛЬЗОВАТЕЛЯ: предотвращаем FOREIGN KEY constraint failed
        $user = User::factory()->create();
        
        $event = Event::factory()->create(['user_id' => $user->id]);
        $date = Carbon::parse('2026-03-10 12:00:00');
        $repository = new EventInstanceRepository();

        $data = [
            'event_id'          => $event->id,
            'original_start_at' => $date,
            'title'             => 'Initial Title',
            'is_overridden'     => true,
        ];

        $repository->updateOrCreateOverride($data);
        
        $data['title'] = 'Updated Title';
        $repository->updateOrCreateOverride($data);

        $this->assertDatabaseCount('calendar_event_instances', 1);
        $this->assertDatabaseHas('calendar_event_instances', [
            'title' => 'Updated Title'
        ]);
    }
}