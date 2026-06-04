<?php

declare(strict_types=1);

namespace Chronos\EventCore\Database\Factories;

use Chronos\EventCore\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

/**
 * Class EventFactory
 *
 * Фабрика для генерации тестовых данных модели Event.
 * Используется для наполнения БД и в автоматизированных тестах.
 * * @extends Factory<Event>
 * * @property-read string $id Уникальный идентификатор события
 * @property-read int $user_id Владелец события
 * @property-read int|null $category_id Категория
 * @property-read string $title Заголовок
 * @property-read string|null $description Описание
 * @property-read string $color_accent HEX-цвет
 * @property-read string|null $recurrence_rule Правило повторения
 * @property-read Carbon $starts_at Время начала
 * @property-read Carbon $ends_at Время окончания
 */
class EventFactory extends Factory
{
    /**
     * Имя соответствующей модели.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Определение состояния модели по умолчанию.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Создаем случайную дату начала в пределах текущего месяца
        $startsAt = Carbon::instance($this->faker->dateTimeBetween('now', '+1 month'))
            ->setMinute(0)
            ->setSecond(0);

        return [
            'id'              => Str::uuid()->toString(),
            'user_id'         => 1, // ID пользователя-владельца (предполагаем наличие)
            'category_id'     => null,
            'title'           => $this->faker->sentence(3),
            'description'     => $this->faker->realText(200),
            'color_accent'    => $this->faker->hexColor(),
            'recurrence_rule' => null, // По умолчанию одиночное событие
            'starts_at'       => $startsAt,
            'ends_at'         => $startsAt->copy()->addHours(rand(1, 3)),
        ];
    }

    /**
     * Состояние: повторяющееся событие (еженедельно).
     *
     * @return self
     */
    public function weekly(): self
    {
        return $this->state(fn (array $attributes) => [
            'recurrence_rule' => 'FREQ=WEEKLY;BYDAY=MO;COUNT=10',
        ]);
    }
}
