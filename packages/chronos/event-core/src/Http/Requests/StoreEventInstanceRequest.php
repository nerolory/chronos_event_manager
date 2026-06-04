<?php

declare(strict_types=1);

namespace Chronos\EventCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreEventInstanceRequest
 * * Валидирует данные для создания или обновления исключения (override) 
 * конкретного вхождения события.
 */
class StoreEventInstanceRequest extends FormRequest
{
    /**
     * Определить, разрешено ли пользователю выполнять этот запрос.
     */
    public function authorize(): bool
    {
        // Логика авторизации будет добавлена позже (например, проверка владельца события)
        return true;
    }

    /**
     * Правила валидации для входящего запроса.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            // Идентификатор базового события
            'event_id' => ['required', 'uuid', 'exists:calendar_events,id'],

            // Якорь: оригинальное время начала вхождения из сетки RRULE
            'original_start_at' => ['required', 'date'],

            // Опциональные поля для переопределения
            'title'    => ['nullable', 'string', 'max:255'],
            'start_at' => ['nullable', 'date'],
            'ends_at'  => ['nullable', 'date', 'after:start_at'],

            // Флаги состояния
            'is_cancelled' => ['nullable', 'boolean'],
            'is_completed' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Пользовательские сообщения об ошибках.
     * * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'original_start_at.required' => 'Необходимо указать оригинальное время начала вхождения.',
            'event_id.exists' => 'Указанное базовое событие не найдено.',
            'ends_at.after' => 'Время окончания должно быть позже времени начала.',
        ];
    }
}