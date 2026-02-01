<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangelogUserRead extends Model
{
    // Permitir preenchimento em massa para esses campos
    protected $fillable = [
        'store_user_id',
        'changelog_id'
    ];

    /**
     * Relacionamento reverso: uma leitura pertence a uma novidade.
     */
    public function changelog(): BelongsTo
    {
        return $this->belongsTo(Changelog::class);
    }
}
