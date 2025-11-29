<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'store_users'; // Conecta à tabela correta (criada via SQL/migration)

    /**
     * Os campos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'current_store_id', // FK para a Store (NULLABLE)
        'name',
        'surname',
        'login',
        'email',
        'password',
        'document_number',
        'id_document_number', // RG/ID
        'phone_number',
        'is_active',
        'social_name',
        'company_phone',
    ];

    /**
     * Os atributos que devem ser escondidos (segurança).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversão de tipos de dados.
     */
    protected $casts = [
        'is_active' => 'boolean',
        // 'config_json' => 'array', // Removido pois não está no schema atual, evitamos o erro
    ];

    /**
     * Relacionamento: Este usuário pertence à loja (ou é o dono atual).
     */
    public function store(): BelongsTo
    {
        // Define o relacionamento com a tabela 'stores' usando a FK correta
        return $this->belongsTo(Store::class, 'current_store_id'); 
    }
}