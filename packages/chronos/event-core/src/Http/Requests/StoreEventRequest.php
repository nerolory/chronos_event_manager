<?php

declare(strict_types=1);

namespace Chronos\EventCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreEventRequest
 *
 * Валидирует данные для создания нового события.
 */
class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'category_id'      => ['nullable', 'integer', 'exists:event_categories,id'],
            'color_accent'     => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'starts_at'        => ['required', 'date'],
            'ends_at'          => ['required', 'date', 'after:starts_at'],
            'recurrence_rule'  => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'       => 'Название события обязательно.',
            'ends_at.after'        => 'Время окончания должно быть позже времени начала.',
            'color_accent.regex'   => 'Цвет должен быть в формате HEX (#RRGGBB).',
            'category_id.exists'   => 'Указанная категория не найдена.',
        ];
    }
}
