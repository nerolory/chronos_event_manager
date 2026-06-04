<?php

namespace App\Repositories;

use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class UserSettingsRepository
{
    /**
     * Получение настроек пользователя
     */
    public function getUserSettings(int $userId): Collection
    {
        return UserSetting::where('user_id', $userId)->get();
    }

    /**
     * Обновление настроек пользователя
     * @param SupportCollection<SupportCollection> $settings
     */
    public function updateUserSettings(int $userId, SupportCollection $settings): void
    {
        foreach ($settings as $setting) {
            UserSetting::updateOrCreate(
                [
                    'user_id' => $userId,
                    'key' => $setting['key'],
                ],
                [
                    'value' => $setting['value'],
                ]
            );
        }
    }
}
