<?php

declare(strict_types=1);

namespace Chronos\EventCore\Services;

use Chronos\EventCore\Models\Event;
use Chronos\EventCore\Models\CalendarEventInstance;
use Chronos\EventCore\Parsers\RRuleParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

/**
 * Class EventOccurrenceService
 */
class EventOccurrenceService
{
    /**
     * Рассчитать все вхождения события для указанного периода.
     *
     * @param Event $event
     * @param Carbon $rangeStart
     * @param Carbon $rangeEnd
     * @return Collection<int, array>
     */
    public function getOccurrencesForPeriod(Event $event, Carbon $rangeStart, Carbon $rangeEnd): Collection
    {
        // Используем CalendarEventInstance для поиска переопределений
        $overrides = CalendarEventInstance::where('event_id', $event->id)
            ->whereBetween('original_start_at', [$rangeStart, $rangeEnd])
            ->get()
            ->keyBy(fn($item) => $item->original_start_at->toDateTimeString());

        if ($event->recurrence_rule) {
            $dates = RRuleParser::getOccurrences(
                $event->recurrence_rule,
                $event->starts_at,
                $rangeStart,
                $rangeEnd
            );

            $duration = $event->starts_at->diff($event->ends_at);

            return $dates->map(function (\Carbon\Carbon $date) use ($event, $overrides, $duration) {
                $anchor = $date->toDateTimeString();
                return $this->formatOccurrence(
                    $event,
                    $date,
                    $overrides->get($anchor),
                    $duration
                );
            })->filter(fn($occ) => !($occ['is_cancelled'] ?? false));
        }

        // Логика для одиночного события
        $anchor = $event->starts_at->toDateTimeString();
        $isIntersecting = $event->starts_at->lt($rangeEnd) && $event->ends_at->gt($rangeStart);

        if ($isIntersecting) {
            return collect([
                $this->formatOccurrence(
                    $event,
                    $event->starts_at,
                    $overrides->get($anchor)
                )
            ]);
        }

        return collect();
    }

    /**
     * Формирование данных вхождения.
     */
    private function formatOccurrence(Event $event, \Carbon\Carbon $currentDate, ?CalendarEventInstance $override, $duration = null): array
    {
        return [
            'event_id'      => $event->id,
            'title'         => $override?->title ?? $event->title,
            'start'         => $override?->start_at ?? $currentDate,
            'end'           => $override?->ends_at ?? ($duration ? $currentDate->copy()->add($duration) : $event->ends_at),
            'color'         => $event->color_accent,
            'is_overridden' => $override !== null,
            'is_cancelled'  => $override?->is_cancelled ?? false,
            'is_completed'  => $override?->is_completed ?? false,
        ];
    }
}