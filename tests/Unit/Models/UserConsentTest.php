<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\ConsentType;
use App\Models\UserConsent;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserConsentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест: Проверка связи согласия с пользователем и типом.
     */
    public function test_user_consent_belongs_to_user_and_type(): void
    {
        // 1. Подготовка данных
        $user = User::factory()->create();
        
        // Создаем настройки (согласно НТЗ 2.1 и вашей модели UserSetting)
        UserSetting::create([
            'user_id' => $user->id,
            'timezone' => 'Europe/Moscow'
        ]);
        
        $consentType = ConsentType::create([
            'slug' => 'privacy-policy',
            'title' => 'Политика конфиденциальности',
            'is_required' => true
        ]);

        $userConsent = UserConsent::create([
            'user_id' => $user->id,
            'consent_type_id' => $consentType->id,
            'is_granted' => true,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test',
            'created_at' => now()
        ]);

        // 2. Проверки
        $this->assertInstanceOf(User::class, $userConsent->user);
        $this->assertEquals($user->id, $userConsent->user->id);
        
        // Используем метод type() из вашей модели UserConsent.php
        $this->assertInstanceOf(ConsentType::class, $userConsent->type);
        $this->assertEquals('privacy-policy', $userConsent->type->slug);
        
        // Дополнительно проверим наличие настроек у юзера
        $this->assertEquals('Europe/Moscow', $user->settings->timezone);
    }

    /**
     * Тест: Пользователь может иметь несколько согласий.
     */
    public function test_user_can_have_multiple_consents(): void
    {
        $user = User::factory()->create();
        $type1 = ConsentType::create(['slug' => 'pdp', 'title' => 'ПДП', 'is_required' => true]);
        $type2 = ConsentType::create(['slug' => 'marketing', 'title' => 'Маркетинг', 'is_required' => false]);

        UserConsent::create([
            'user_id' => $user->id, 
            'consent_type_id' => $type1->id, 
            'is_granted' => true,
            'ip_address' => '127.0.0.1',
            'created_at' => now()
        ]);
        
        UserConsent::create([
            'user_id' => $user->id, 
            'consent_type_id' => $type2->id, 
            'is_granted' => false,
            'ip_address' => '127.0.0.1',
            'created_at' => now()
        ]);

        // Проверяем связь consents() из вашей модели User.php
        $this->assertCount(2, $user->refresh()->consents);
    }
}