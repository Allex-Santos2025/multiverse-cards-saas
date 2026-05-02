<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasEmailVerificationWizard;

class PlayerUser extends Authenticatable implements MustVerifyEmail
{
    use HasEmailVerificationWizard;
    use HasFactory, Notifiable;

    /**
     * O 'guard' específico para jogadores.
     */
    protected $guard = 'web'; 

    protected $fillable = [
        'name', 
        'surname', 
        'nickname', 
        'email', 
        'password', 
        'document_number', 
        'id_document_number', // <--- RG liberado
        'phone_number', 
        'birth_date',         // <--- Data de Nascimento liberada
        'preferred_language', 
        'loyalty_points',
        'data_json', 
        'is_active',
        'balance',            // <--- Carteira liberada
        'pix_key'             // <--- Pix liberado
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'data_json' => 'array',
    ];

    /**
     * Helper para pegar o nome completo do jogador.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function addresses()
    {
        return $this->hasMany(PlayerAddress::class, 'player_user_id');
    }
}