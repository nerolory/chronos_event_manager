<?php

declare(strict_types=1);

namespace Chronos\EventCore\Services;

use Chronos\EventCore\Models\CalendarEventInstance;
use Chronos\EventCore\Repositories\EventInstanceRepository;
use Illuminate\Support\Carbon;

/**
 * Class EventInstanceService
 * Ответственен за управление исключениями в сериях событий.
 */
class EventInstanceService
{
    public function __construct(
        protected EventInstanceRepository $repository
    ) {}

    /**
     * Создает или обновляет исключение.
     *
     * @param array $data
     * @return CalendarEventInstance
     */
    public function upsertOverride(array $data): CalendarEventInstance
    {
        // Преобразуем строку в объект Carbon для корректной работы с репозиторием
        $data['original_start_at'] = Carbon::parse($data['original_start_at']);

        return $this->repository->updateOrCreateOverride($data);
    }
}