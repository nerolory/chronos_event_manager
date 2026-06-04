<?php

declare(strict_types=1);

namespace Chronos\EventCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class CalendarEventInstance
 *
 * Модель для хранения исключений и переопределений конкретных вхождений рекурсивных событий.
 * Соответствует таблице calendar_event_instances.
 *
 * @property int $id
 * @property string $event_id
 * @property Carbon $original_start_at Якорь для связи с RRULE
 * @property string|null $title Переопределенный заголовок
 * @property Carbon|null $start_at Новое время начала
 * @property Carbon|null $ends_at Новое время окончания
 * @property bool $is_cancelled Флаг отмены конкретного вхождения
 * @property bool $is_completed Флаг завершения
 */
class CalendarEventInstance extends Model
{
    /**
     * @var string
     */
    protected $table = 'calendar_event_instances';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'original_start_at',
        'title',
        'start_at',
        'ends_at',
        'is_cancelled',
        'is_completed',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'original_start_at' => 'datetime',
        'start_at'          => 'datetime',
        'ends_at'           => 'datetime',
        'is_cancelled'      => 'boolean',
        'is_completed'      => 'boolean',
    ];

    /**
     * Обратная связь с базовым событием.
     *
     * @return BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}