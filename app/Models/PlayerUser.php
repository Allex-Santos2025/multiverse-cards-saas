<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\HasTenants; 
use Illuminate\Database\Eloquent\Collection; 
use Spatie\Permission\Traits\HasRoles; 
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Database\Eloquent\Model; 
use App\Models\Store; 
use Filament\Panel;

class PlayerUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'player_users'; // Conecta à tabela correta

    /**
     * Os campos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'name',
        'surname',
        'login',
        'email',
        'password',
        'document_number',       // CPF/CNPJ
        'id_document_number',    // RG/ID
        'phone_number',
        'birth_date',
        'zip_code',
        'preferred_language',
        'loyalty_points',
        'is_active',
        'data_json',
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
        'birth_date' => 'date',
        'data_json' => 'array',
        'loyalty_points' => 'integer',
    ];
}