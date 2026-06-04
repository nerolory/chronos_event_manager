<?php

declare(strict_types=1);

namespace Chronos\EventCore\Tests\Feature;

use App\Models\User;
use Chronos\EventCore\Models\Event;
use Chronos\EventCore\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

/**
 * Class EventApiTest
 *
 * Feature-тесты для CRUD API событий пакета.
 * Проверяют авторизацию, создание, обновление, удаление и получение сетки.
 */
class EventApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/chronos/events?start=2026-03-01&end=2026-03-31');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_event_grid(): void
    {
        Event::factory()->create([
            'user_id'    => $this->user->id,
            'title'      => 'Grid Event',
            'starts_at'  => Carbon::parse('2026-03-15 10:00:00'),
            'ends_at'    => Carbon::parse('2026-03-15 11:00:00'),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/chronos/events?start=2026-03-01&end=2026-03-31');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['event_id', 'title', 'start', 'end', 'color', 'is_completed', 'is_overridden'],
                ],
            ]);
    }

    public function test_user_cannot_see_other_users_events(): void
    {
        $otherUser = User::factory()->create();
        Event::factory()->create([
            'user_id'   => $otherUser->id,
            'starts_at' => Carbon::parse('2026-03-15 10:00:00'),
            'ends_at'   => Carbon::parse('2026-03-15 11:00:00'),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/chronos/events?start=2026-03-01&end=2026-03-31');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_event_grid_requires_start_and_end(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/chronos/events');

        $response->assertStatus(422);
    }

    public function test_user_can_create_event(): void
    {
        $payload = [
            'title'       => 'New Event',
            'starts_at'   => '2026-04-01T10:00:00',
            'ends_at'     => '2026-04-01T11:00:00',
            'color_accent' => '#FF5733',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/chronos/events', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'New Event')
            ->assertJsonPath('data.color_accent', '#FF5733');

        $this->assertDatabaseHas('calendar_events', [
            'title'   => 'New Event',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_event_validation_fails_without_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chronos/events', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'starts_at', 'ends_at']);
    }

    public function test_user_can_update_own_event(): void
    {
        $event = Event::factory()->create([
            'user_id'   => $this->user->id,
            'title'     => 'Old Title',
            'starts_at' => Carbon::parse('2026-04-01 10:00:00'),
            'ends_at'   => Carbon::parse('2026-04-01 11:00:00'),
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/chronos/events/{$event->id}", ['title' => 'Updated Title']);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('calendar_events', ['id' => $event->id, 'title' => 'Updated Title']);
    }

    public function test_user_cannot_update_others_event(): void
    {
        $otherUser = User::factory()->create();
        $event = Event::factory()->create([
            'user_id'   => $otherUser->id,
            'starts_at' => Carbon::parse('2026-04-01 10:00:00'),
            'ends_at'   => Carbon::parse('2026-04-01 11:00:00'),
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/chronos/events/{$event->id}", ['title' => 'Hacked']);

        $response->assertStatus(403);
    }

    public function test_user_can_soft_delete_own_event(): void
    {
        $event = Event::factory()->create([
            'user_id'   => $this->user->id,
            'starts_at' => Carbon::parse('2026-04-01 10:00:00'),
            'ends_at'   => Carbon::parse('2026-04-01 11:00:00'),
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/chronos/events/{$event->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('calendar_events', ['id' => $event->id]);
    }

    public function test_user_cannot_delete_others_event(): void
    {
        $otherUser = User::factory()->create();
        $event = Event::factory()->create([
            'user_id'   => $otherUser->id,
            'starts_at' => Carbon::parse('2026-04-01 10:00:00'),
            'ends_at'   => Carbon::parse('2026-04-01 11:00:00'),
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/chronos/events/{$event->id}");

        $response->assertStatus(403);
    }

    public function test_recurring_event_appears_in_grid(): void
    {
        Event::factory()->create([
            'user_id'          => $this->user->id,
            'title'            => 'Weekly Monday',
            'starts_at'        => Carbon::parse('2026-03-02 09:00:00'),
            'ends_at'          => Carbon::parse('2026-03-02 10:00:00'),
            'recurrence_rule'  => 'FREQ=WEEKLY;BYDAY=MO;COUNT=4',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/chronos/events?start=2026-03-01&end=2026-03-31');

        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data'));
    }
}
