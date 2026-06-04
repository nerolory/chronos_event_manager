<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConsentTypeResource;
use App\Services\ConsentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Exception;

class ConsentController extends Controller
{
    public function __construct(
        protected ConsentService $service
    ) {
    }

    /**
     * Получить список согласий для регистрации
     */
    public function index(): AnonymousResourceCollection|JsonResponse
    {
        try {
            $consents = $this->service->getConsentsForRegistration();

            return ConsentTypeResource::collection($consents);
        } catch (Exception $e) {
            Log::error('Failed to get consent types', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось получить список согласий',
            ], 500);
        }
    }
}
