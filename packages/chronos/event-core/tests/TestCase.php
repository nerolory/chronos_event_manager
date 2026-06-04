<?php

declare(strict_types=1);

namespace Chronos\EventCore\Tests;

use Tests\TestCase as BaseTestCase;
use Chronos\EventCore\Providers\EventServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class TestCase
 *
 * Базовый класс для тестов пакета. Наследуется от системного TestCase.
 *
 * @package Chronos\EventCore\Tests
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Настройка окружения перед каждым тестом.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 1. Регистрируем провайдер, если он не подтянулся автоматически
        if (! app()->providerIsLoaded(EventServiceProvider::class)) {
            app()->register(EventServiceProvider::class);
        }

        // 2. Вместо loadMigrationsFrom используем системный путь для миграций пакета
        $this->artisan('migrate', [
            '--path' => 'packages/chronos/event-core/database/migrations',
            '--realpath' => true,
        ]);
    }
}