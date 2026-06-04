<?php

declare(strict_types=1);

namespace Chronos\EventCore\Repositories;

use Chronos\EventCore\Models\Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EventRepository
{
    /**
     * Получить все активные события конкретного пользователя.
     */
    public function getForUser(int $userId): Collection
    {
        return Event::where('user_id', $userId)->get();
    }

    /**
     * Получить события пользователя, которые пересекаются с заданным периодом.
     * Включает события с RRULE — они фильтруются в сервисе.
     */
    public function getForUserInPeriod(int $userId, Carbon $start, Carbon $end): Collection
    {
        return Event::where('user_id', $userId)
            ->where(function ($query) use ($start, $end) {
                $query
                    ->whereNotNull('recurrence_rule')
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->whereNull('recurrence_rule')
                          ->where('starts_at', '<', $end)
                          ->where('ends_at', '>', $start);
                    });
            })
            ->get();
    }

    /**
     * Создать новое событие.
     *
     * @param array<string, mixed> $data
     */
    public function create(int $userId, array $data): Event
    {
        return Event::create(array_merge($data, ['user_id' => $userId]));
    }

    /**
     * Обновить существующее событие.
     *
     * @param array<string, mixed> $data
     */
    public function update(Event $event, array $data): Event
    {
        $event->update($data);

        return $event->fresh();
    }

    /**
     * Мягкое удаление события (SoftDeletes).
     */
    public function delete(Event $event): void
    {
        $event->delete();
    }
}