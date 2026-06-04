<?php

declare(strict_types=1);

namespace Chronos\EventCore\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * Class EventOccurrenceResource
 *
 * Ресурс для преобразования вхождения события в формат, пригодный для отображения в календаре.
 * Обрабатывает как базовые данные родительского события, так и специфичные данные переопределенного вхождения.
 *
 * @property-read object $resource {
 * @property \Chronos\EventCore\Models\Event $event
 * @property string $title
 * @property Carbon $start
 * @property Carbon $end
 * @property string $color
 * @property bool $is_completed
 * @property bool $is_overridden
 * }
 * @package Chronos\EventCore\Http\Resources
 */
class EventOccurrenceResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив для фронтенда.
     *
     * @param Request $request
     * @return array{
     * event_id: string,
     * title: string,
     * start: string,
     * end: string,
     * color: string,
     * description: string|null,
     * is_completed: bool,
     * is_overridden: bool
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'event_id'      => $this->resource->event->id,
            'title'         => $this->resource->title,
            // Используем стандарт ISO 8601 для фронтенда
            'start'         => $this->resource->start->toIso8601String(),
            'end'           => $this->resource->end->toIso8601String(),
            'color'         => $this->resource->color,
            'description'   => $this->resource->event->description,
            'is_completed'  => $this->resource->is_completed,
            'is_overridden' => $this->resource->is_overridden,
        ];
    }
}
