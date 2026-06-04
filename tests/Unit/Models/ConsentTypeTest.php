<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\ConsentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест: Проверка создания типа согласия и его атрибутов.
     */
    public function test_consent_type_attributes(): void
    {
        $consent = ConsentType::create([
            'slug' => 'web-push',
            'title' => 'Разрешить пуши',
            'is_required' => false
        ]);

        $this->assertEquals('web-push', $consent->slug);
        $this->assertFalse((bool)$consent->is_required);
    }

    /**
     * Тест: Поиск обязательных согласий (Scope или Query).
     */
    public function test_can_filter_required_consents(): void
    {
        ConsentType::create(['slug' => 'req', 'title' => 'Must', 'is_required' => true]);
        ConsentType::create(['slug' => 'opt', 'title' => 'Maybe', 'is_required' => false]);

        $requiredCount = ConsentType::where('is_required', true)->count();
        $this->assertEquals(1, $requiredCount);
    }
}