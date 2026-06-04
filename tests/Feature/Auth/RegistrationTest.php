<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\ConsentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест: Успешная регистрация.
     * Исправлено: передаем слоги (slugs) в качестве ключей, как того ожидает AuthService.
     */
    public function test_user_can_register_with_consents_and_timezone(): void
    {
        $this->withoutVite();

        // 1. Создаем тип согласия с конкретным слагом
        $privacy = ConsentType::create([
            'slug' => 'privacy-policy',
            'title' => 'Политика',
            'is_required' => true
        ]);

        // 2. Данные для запроса
        // ВАЖНО: Ключом должен быть slug 'privacy-policy', а не ID
        $registrationData = [
            'name' => 'Ivan Ivanov',
            'email' => 'ivan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'timezone' => 'Europe/Moscow',
            'consents' => [
                'privacy-policy' => true,
            ]
        ];

        // 3. Отправляем запрос на ваш роут
        $response = $this->postJson('/register-web', $registrationData);

        // 4. Проверки
        $response->assertStatus(201);
        $this->assertAuthenticated();

        $user = User::where('email', 'ivan@example.com')->first();

        // Проверяем настройки
        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'timezone' => 'Europe/Moscow'
        ]);

        // Проверяем согласия (теперь таблица не будет пустой)
        $this->assertDatabaseHas('user_consents', [
            'user_id' => $user->id,
            'consent_type_id' => $privacy->id,
            'is_granted' => true
        ]);
    }

    /**
     * Тест: Ошибка валидации
     */
    public function test_registration_validation_error(): void
    {
        $this->withoutVite();
        $response = $this->postJson('/register-web', []);
        $response->assertStatus(422);
    }
}