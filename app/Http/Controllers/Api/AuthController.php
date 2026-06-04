<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register(
                collect($request->validated()),
                $request->ip() ?? '127.0.0.1',
                $request->userAgent() ?? 'Unknown'
            );

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => UserResource::make($user->load('settings')),
                ],
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to register user', [
                'email' => $request->validated()['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось зарегистрировать пользователя',
            ], 500);
        }
    }

    /**
     * Авторизация пользователя (WEB/Session)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();

            // Пытаемся войти с сохранением сессии
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                return response()->json([
                    'success' => true,
                    'message' => 'Авторизация успешна',
                ]);
            }

            // Если данные неверны, возвращаем ошибку валидации для Vue
            throw ValidationException::withMessages([
                'email' => ['Неверный адрес почты или пароль.'],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Failed to login user', [
                'email' => $request->validated()['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось авторизоваться',
            ], 500);
        }
    }
}
