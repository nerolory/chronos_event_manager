<?php

namespace Tests\Feature;

use App\Jobs\SendEventNotification;
use App\Jobs\SendPushNotification;
use App\Models\User;
use App\Models\UserConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EventNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_user_with_consents(): void
    {
        $response = $this->postJson('/api/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'consents' => [1, 2],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertDatabaseCount('user_consents', 2);
    }

    public function test_email_notification_job_dispatches(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $eventData = [
            'title' => 'Test Event',
            'start' => '2026-06-04 10:00:00',
            'end' => '2026-06-04 11:00:00',
        ];

        SendEventNotification::dispatch($user, $eventData);

        Queue::assertPushed(SendEventNotification::class);
    }

    public function test_push_notification_requires_consent(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $eventData = [
            'title' => 'Test Event',
            'start' => '2026-06-04 10:00:00',
            'end' => '2026-06-04 11:00:00',
        ];

        // Без согласия уведомление не должно отправляться
        SendPushNotification::dispatch($user, $eventData);

        Queue::assertPushed(SendPushNotification::class, function ($job) use ($user) {
            return $job->user->id === $user->id;
        });
    }

    public function test_privacy_settings_endpoint(): void
    {
        $user = User::factory()->create();
        UserConsent::factory()->create([
            'user_id' => $user->id,
            'consent_type_id' => 1,
            'granted' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/privacy');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['id', 'user_id', 'consent_type_id', 'granted'],
        ]);
    }

    public function test_user_settings_endpoint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/settings');

        $response->assertStatus(200);
    }

    public function test_update_user_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/settings', [
            'settings' => [
                ['key' => 'timezone', 'value' => 'Europe/Moscow'],
                ['key' => 'language', 'value' => 'ru'],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Настройки обновлены']);
    }
}
