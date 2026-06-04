<?php

declare(strict_types=1);

namespace Chronos\EventCore\Http\Requests;

use Chronos\EventCore\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateEventRequest
 *
 * Валидирует данные для обновления события.
 * Все поля — опциональные (PATCH-семантика), но проверяем владение.
 */
class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $event && (int) $event->user_id === (int) auth()->id();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title'           => ['sometimes', 'required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
            'category_id'     => ['nullable', 'integer', 'exists:event_categories,id'],
            'color_accent'    => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'starts_at'       => ['sometimes', 'required', 'date'],
            'ends_at'         => ['sometimes', 'required', 'date', 'after:starts_at'],
            'recurrence_rule' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ends_at.after'      => 'Время окончания должно быть позже времени начала.',
            'color_accent.regex' => 'Цвет должен быть в формате HEX (#RRGGBB).',
            'category_id.exists' => 'Указанная категория не найдена.',
        ];
    }
}
