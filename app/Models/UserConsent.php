<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserConsent extends Model
{
    /**
     * Таблица не использует стандартные timestamps (updated_at),
     * так как это журнал фактов, которые не редактируются.
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'consent_type_id',
        'granted',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'granted' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с типом согласия (что именно было принято)
     */
    public function consentType(): BelongsTo
    {
        return $this->belongsTo(ConsentType::class, 'consent_type_id');
    }
}
