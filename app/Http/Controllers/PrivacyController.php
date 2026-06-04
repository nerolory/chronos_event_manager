<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePrivacyRequest;
use App\Http\Resources\UserConsentResource;
use App\Services\ConsentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Exception;

class PrivacyController extends Controller
{
    public function __construct(
        private ConsentService $consentService
    ) {
    }

    public function index(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $consents = $this->consentService->getUserConsents(auth()->user()->id);

            return UserConsentResource::collection($consents);
        } catch (Exception $e) {
            Log::error('Failed to get user consents', [
                'user_id' => auth()->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось получить согласия пользователя',
            ], 500);
        }
    }

    public function update(UpdatePrivacyRequest $request): JsonResponse
    {
        try {
            $this->consentService->updateUserConsents(
                auth()->user()->id,
                collect($request->validated()['consents'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Согласия обновлены',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update user consents', [
                'user_id' => auth()->user()->id,
                'consents' => $request->validated()['consents'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось обновить согласия',
            ], 500);
        }
    }
}
