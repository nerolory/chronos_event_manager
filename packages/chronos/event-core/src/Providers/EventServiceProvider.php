<?php

declare(strict_types=1);

namespace Chronos\EventCore\Providers;

use Illuminate\Support\ServiceProvider;
use Chronos\EventCore\Services\EventOccurrenceService;

/**
 * Class EventServiceProvider
 *
 * Главный сервис-провайдер пакета Chronos Event Core.
 * Регистрирует компоненты системы, маршруты и миграции.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов пакета в контейнере.
     *
     * @return void
     */
    public function register(): void
    {
        // Слияние конфига пакета с хост-конфигом (хост может переопределить через config/chronos.php)
        $this->mergeConfigFrom(__DIR__ . '/../../config/chronos.php', 'chronos');

        // Регистрация сервиса как Singleton для обеспечения единой точки расчета
        $this->app->singleton(EventOccurrenceService::class, function ($app) {
            return new EventOccurrenceService();
        });
    }

    /**
     * Загрузка ресурсов пакета (миграции, роуты, публикуемые файлы).
     *
     * @return void
     */
    public function boot(): void
    {
        // Загружаем миграции из пакета — они применяются при php artisan migrate
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Регистрация API маршрутов пакета
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');

        // Публикуемые ресурсы для хост-приложения (vendor:publish)
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'chronos-migrations');

        $this->publishes([
            __DIR__ . '/../../config/chronos.php' => config_path('chronos.php'),
        ], 'chronos-config');
    }
}
