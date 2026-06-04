<?php

namespace App\Services;

use App\Repositories\ConsentRepository;
use App\Repositories\UserConsentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class ConsentService
{
    public function __construct(
        protected ConsentRepository $repository,
        protected UserConsentRepository $userConsentRepository
    ) {
    }

    /**
     * Получение списка согласий для формы регистрации
     */
    public function getConsentsForRegistration(): Collection
    {
        return $this->repository->getAllActive();
    }

    /**
     * Получение согласий пользователя
     */
    public function getUserConsents(int $userId): Collection
    {
        return $this->userConsentRepository->getUserConsents($userId);
    }

    /**
     * Обновление согласий пользователя
     * @param SupportCollection<SupportCollection> $consents
     */
    public function updateUserConsents(int $userId, SupportCollection $consents): void
    {
        $this->userConsentRepository->updateUserConsents($userId, $consents);
    }
}
