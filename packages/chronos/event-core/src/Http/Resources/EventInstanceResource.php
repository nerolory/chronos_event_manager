<?php

declare(strict_types=1);

namespace Chronos\EventCore\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class EventInstanceResource
 * * Преобразует модель исключения (CalendarEventInstance) в JSON-структуру.
 * * @property-read \Chronos\EventCore\Models\CalendarEventInstance $resource
 * @package Chronos\EventCore\Http\Resources
 */
class EventInstanceResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->resource->id,
            'event_id'          => $this->resource->event_id,
            'original_start_at' => $this->resource->original_start_at->toIso8601String(),
            'title'             => $this->resource->title,
            'start_at'          => $this->resource->start_at?->toIso8601String(),
            'ends_at'           => $this->resource->ends_at?->toIso8601String(),
            'is_cancelled'      => $this->resource->is_cancelled,
            'is_completed'      => $this->resource->is_completed,
            'updated_at'        => $this->resource->updated_at->toIso8601String(),
        ];
    }
}