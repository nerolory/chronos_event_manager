<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserSettingsRequest;
use App\Http\Resources\UserSettingResource;
use App\Services\UserSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Exception;

class UserSettingsController extends Controller
{
    public function __construct(
        private UserSettingsService $settingsService
    ) {
    }

    public function index(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $settings = $this->settingsService->getUserSettings(auth()->user()->id);

            return UserSettingResource::collection($settings);
        } catch (Exception $e) {
            Log::error('Failed to get user settings', [
                'user_id' => auth()->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось получить настройки пользователя',
            ], 500);
        }
    }

    public function update(UpdateUserSettingsRequest $request): JsonResponse
    {
        try {
            $this->settingsService->updateUserSettings(
                auth()->user()->id,
                collect($request->validated()['settings'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Настройки обновлены',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update user settings', [
                'user_id' => auth()->user()->id,
                'settings' => $request->validated()['settings'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось обновить настройки',
            ], 500);
        }
    }
}
