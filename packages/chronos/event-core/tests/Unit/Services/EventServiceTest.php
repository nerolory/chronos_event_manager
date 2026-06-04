<?php

declare(strict_types=1);

namespace Chronos\EventCore\Tests\Unit\Services;

use Chronos\EventCore\Models\Event;
use Chronos\EventCore\Repositories\EventRepository;
use Chronos\EventCore\Services\EventService;
use Chronos\EventCore\Services\EventOccurrenceService;
use Chronos\EventCore\Tests\TestCase;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;

/**
 * Class EventServiceTest
 *
 * Тестирование EventService с сохранением всех метаданных вхождений.
 *
 * @package Chronos\EventCore\Tests\Unit\Services
 */
class EventServiceTest extends TestCase
{
    /**
     * Тест маппинга событий с проверкой структуры объекта для ресурсов.
     *
     * @return void
     */
    public function test_it_correctly_maps_events_to_occurrences(): void
    {
        $now = Carbon::parse('2026-03-05 10:00:00');
        
        $event = new Event([
            'id'        => 'uuid-1', 
            'title'     => 'Test Event',
            'starts_at' => $now,
            'ends_at'   => $now->copy()->addHour(),
        ]);

        // Создаем мок и принудительно регистрируем его в контейнере Laravel
        $repoMock = $this->mock(EventRepository::class, function (MockInterface $mock) use ($event) {
            $mock->shouldReceive('getForUserInPeriod')
                ->once()
                ->andReturn(collect([$event]));
        });
        app()->instance(EventRepository::class, $repoMock);

        /** @var EventService $service */
        $service = app(EventService::class);

        $result = $service->getEventGridForUser(1, $now->copy()->subDay(), $now->copy()->addDay());

        $this->assertCount(1, $result);
        
        $occurrence = $result->first();
        
        // Сохраняем и проверяем твой свежий код (метаданные)
        $this->assertEquals('Test Event', $occurrence->title);
        $this->assertObjectHasProperty('is_overridden', $occurrence);
        $this->assertObjectHasProperty('is_completed', $occurrence);
    }
}