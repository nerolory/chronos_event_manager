<?php

declare(strict_types=1);

namespace Chronos\EventCore\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class EventResource
 *
 * Трансформирует модель Event в JSON для ответов CRUD API.
 *
 * @property-read \Chronos\EventCore\Models\Event $resource
 */
class EventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->resource->id,
            'user_id'         => $this->resource->user_id,
            'category_id'     => $this->resource->category_id,
            'title'           => $this->resource->title,
            'description'     => $this->resource->description,
            'color_accent'    => $this->resource->color_accent,
            'recurrence_rule' => $this->resource->recurrence_rule,
            'starts_at'       => $this->resource->starts_at->toIso8601String(),
            'ends_at'         => $this->resource->ends_at->toIso8601String(),
            'created_at'      => $this->resource->created_at->toIso8601String(),
            'updated_at'      => $this->resource->updated_at->toIso8601String(),
        ];
    }
}
