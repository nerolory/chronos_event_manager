<?php

declare(strict_types=1);

namespace Chronos\EventCore\Models;

use Chronos\EventCore\Database\Factories\EventFactory;  
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class Event
 * * Модель календарного события. Поддерживает UUID и мягкое удаление.
 *
 * @property string $id Уникальный UUID события
 * @property int $user_id ID владельца события
 * @property int|null $category_id ID категории
 * @property string $title Заголовок события
 * @property string|null $description Описание
 * @property string $color_accent HEX-код цвета
 * @property string|null $recurrence_rule Правило повторения в формате RRULE (RFC 5545)
 * @property Carbon $starts_at Дата и время начала (UTC)
 * @property Carbon $ends_at Дата и время окончания (UTC)
 * @property Carbon|null $deleted_at Дата мягкого удаления
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * * @property-read EventCategory|null $category
 */
class Event extends Model
{
    use HasUuids, SoftDeletes, HasFactory;

    /** @var string */
    protected $table = 'calendar_events';

    /** @var array<int, string> */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'color_accent',
        'recurrence_rule',
        'starts_at',
        'ends_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'recurrence_rule' => 'string',
    ];
    
    /**
    * Создать новый экземпляр фабрики для модели.
    * Именно этот метод делает импорт выше "активным".
    */
    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }

    /**
     * Получить пользователя, которому принадлежит событие.
     * Связь определяется динамически через конфиг auth.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        /** @var string $userModel */
        $userModel = config('auth.providers.users.model');
        
        return $this->belongsTo($userModel);
    }

    /**
     * Категория, к которой относится событие.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }
}
