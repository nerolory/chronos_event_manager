<?php

namespace App\Services;

use App\Repositories\UserSettingsRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class UserSettingsService
{
    public function __construct(
        protected UserSettingsRepository $repository
    ) {
    }

    /**
     * Получение настроек пользователя
     */
    public function getUserSettings(int $userId): Collection
    {
        return $this->repository->getUserSettings($userId);
    }

    /**
     * Обновление настроек пользователя
     * @param SupportCollection<SupportCollection> $settings
     */
    public function updateUserSettings(int $userId, SupportCollection $settings): void
    {
        $this->repository->updateUserSettings($userId, $settings);
    }
}
