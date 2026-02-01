<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'store_id',
        'user_id',
        'user_guard',
        'subject_id',
        'subject_type',
        'action',
        'module',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    /**
     * Converte o campo JSON do banco automaticamente em Array no PHP
     */
    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Relacionamento com a Loja
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relacionamento Polimórfico (O "Coração" da Auditoria)
     * Permite que o log pertença a um Card, Pedido, Cliente, etc.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relacionamento com o Usuário que realizou a ação
     * Como usamos guards diferentes, o ideal é tratar como polimórfico 
     * ou buscar manualmente pelo ID e Guard se necessário.
     */
}