<?php

declare(strict_types=1);

namespace Chronos\EventCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class EventCategory
 *
 * Модель категории события. Принадлежит пользователю (или является системной при user_id = null).
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $title
 * @property string $slug
 * @property string $default_color
 */
class EventCategory extends Model
{
    /** @var array<int, string> */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'default_color',
    ];

    /**
     * Пользователь, которому принадлежит категория.
     * null — системная (общая) категория.
     */
    public function user(): BelongsTo
    {
        /** @var string $userModel */
        $userModel = config('auth.providers.users.model');

        return $this->belongsTo($userModel);
    }

    /**
     * События, относящиеся к данной категории.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'category_id');
    }
}
