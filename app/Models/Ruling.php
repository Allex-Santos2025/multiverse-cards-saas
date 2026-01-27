<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ruling extends Model
{
    use HasFactory;

    /**
     * A "lista de permissões" para o guarda de segurança do Laravel.
     */
    protected $fillable = [
        'mtg_concept_id',
        'source',
        'published_at',
        'comment',
    ];

    /**
     * Define o relacionamento: Um Julgamento pertence a uma CardFunctionality.
     */
    public function cardFunctionality(): BelongsTo
    {
        return $this->belongsTo(CardFunctionality::class);
    }
}