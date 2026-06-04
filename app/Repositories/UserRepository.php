<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    /**
     * @param Collection<string, mixed> $data
     */
    public function create(Collection $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Создаем связанные настройки
        $user->settings()->create([
            'timezone' => $data['timezone'],
            'push_token' => $data['push_token'] ?? null,
        ]);

        return $user;
    }
}
