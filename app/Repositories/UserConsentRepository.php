<?php

namespace App\Repositories;

use App\Models\UserConsent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class UserConsentRepository
{
    /**
     * Массовое сохранение согласий пользователя
     * @param SupportCollection<int> $consentIds
     */
    public function saveConsents(int $userId, SupportCollection $consentIds, string $ip, string $userAgent): void
    {
        foreach ($consentIds as $id) {
            UserConsent::create([
                'user_id' => $userId,
                'consent_type_id' => $id,
                'granted' => true,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Получение согласий пользователя
     */
    public function getUserConsents(int $userId): Collection
    {
        return UserConsent::where('user_id', $userId)
            ->with('consentType')
            ->get();
    }

    /**
     * Обновление согласий пользователя
     * @param SupportCollection<SupportCollection> $consents
     */
    public function updateUserConsents(int $userId, SupportCollection $consents): void
    {
        foreach ($consents as $consent) {
            UserConsent::updateOrCreate(
                [
                    'user_id' => $userId,
                    'consent_type_id' => $consent['consent_type_id'],
                ],
                [
                    'granted' => $consent['granted'],
                ]
            );
        }
    }
}
