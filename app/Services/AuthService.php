<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\ConsentRepository;
use App\Repositories\UserConsentRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected UserConsentRepository $userConsentRepository,
        protected ConsentRepository $consentRepository
    ) {
    }

    /**
     * Регистрация нового пользователя с настройками и согласиями
     * Автоматическая авторизация после создания
     * @param Collection<string, mixed> $data
     * @throws Exception
     */
    public function register(Collection $data, string $ip, string $userAgent): User
    {
        return DB::transaction(function () use ($data, $ip, $userAgent) {
            // 1. Создаем пользователя и его настройки (timezone, push_token)
            $user = $this->userRepository->create($data);

            // 2. Логируем принятые согласия
            if ($data->has('consents') && ! $data['consents']->isEmpty()) {
                // Фильтруем только выбранные согласия (где true)
                $acceptedSlugs = collect($data['consents'])->filter()->keys();

                // Получаем ID по слагам
                $consentIds = $this->consentRepository->getIdsBySlugs($acceptedSlugs);

                if (! $consentIds->isEmpty()) {
                    $this->userConsentRepository->saveConsents(
                        $user->id,
                        $consentIds,
                        $ip,
                        $userAgent
                    );
                }
            }

            // 3. Автоматическая авторизация (Пункт 1 задания)
            Auth::login($user);

            return $user;
        });
    }
}
