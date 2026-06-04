<?php

declare(strict_types=1);

namespace Chronos\EventCore\Services;

use Chronos\EventCore\Repositories\EventRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class EventService
 *
 * Сервис для управления высокоуровневой логикой событий.
 * Обеспечивает сборку сетки событий для пользователя.
 *
 * @package Chronos\EventCore\Services
 */
class EventService
{
    /**
     * @param EventRepository $repository
     * @param EventOccurrenceService $occurrenceService
     */
    public function __construct(
        protected EventRepository $repository,
        protected EventOccurrenceService $occurrenceService
    ) {}

    /**
     * Получает события и превращает их в плоский список вхождений для календаря.
     * Обогащает данные информацией об исключениях.
     *
     * @param int $userId Идентификатор владельца событий.
     * @param Carbon $start Начало периода.
     * @param Carbon $end Конец периода.
     * @return Collection<int, object>
     */
    public function getEventGridForUser(int $userId, Carbon $start, Carbon $end): Collection
    {
        $events = $this->repository->getForUserInPeriod($userId, $start, $end);
        $grid = collect();

        foreach ($events as $event) {
            $occurrences = $this->occurrenceService->getOccurrencesForPeriod($event, $start, $end);

            foreach ($occurrences as $occ) {
                $grid->push((object)[
                    'event'         => $event,
                    'title'         => $occ['title'],
                    'start'         => $occ['start'],
                    'end'           => $occ['end'],
                    'color'         => $occ['color'],
                    'is_completed'  => $occ['is_completed'],
                    'is_overridden' => $occ['is_overridden'],
                ]);
            }
        }

        return $grid;
    }

    /**
     * Создать новое событие для пользователя.
     *
     * @param int $userId
     * @param array<string, mixed> $data
     * @return \Chronos\EventCore\Models\Event
     */
    public function createEvent(int $userId, array $data): \Chronos\EventCore\Models\Event
    {
        return $this->repository->create($userId, $data);
    }

    /**
     * Обновить событие (только владелец).
     *
     * @param \Chronos\EventCore\Models\Event $event
     * @param array<string, mixed> $data
     * @return \Chronos\EventCore\Models\Event
     */
    public function updateEvent(\Chronos\EventCore\Models\Event $event, array $data): \Chronos\EventCore\Models\Event
    {
        return $this->repository->update($event, $data);
    }

    /**
     * Мягко удалить событие.
     *
     * @param \Chronos\EventCore\Models\Event $event
     * @return void
     */
    public function deleteEvent(\Chronos\EventCore\Models\Event $event): void
    {
        $this->repository->delete($event);
    }
}
