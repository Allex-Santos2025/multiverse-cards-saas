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
        'provider_name',
        'provider_id',
        'avatar',
        'email_verified_at',
        'document_number',
        'phone_number',
        'birth_date',
        'is_active',
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
}