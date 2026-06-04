<?php

declare(strict_types=1);

namespace Chronos\EventCore\Repositories;

use Chronos\EventCore\Models\CalendarEventInstance;

class EventInstanceRepository
{
    /**
     * Выполняет Upsert операцию для исключения.
     *
     * @param array $data
     * @return CalendarEventInstance
     */
    public function updateOrCreateOverride(array $data): CalendarEventInstance
    {
        return CalendarEventInstance::updateOrCreate(
            [
                'event_id'          => $data['event_id'],
                'original_start_at' => $data['original_start_at'],
            ],
            $data
        );
    }
}