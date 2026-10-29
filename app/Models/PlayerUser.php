<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasEmailVerificationWizard;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'id_document_number',
        'phone_number', 
        'birth_date',
        'preferred_language', 
        'loyalty_points',
        'data_json', 
        'is_active',
        'balance',
        'pix_key'
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
        return "{$this->name} {$this->surname}";
    }

    public function addresses()
    {
        return $this->hasMany(PlayerAddress::class, 'player_user_id');
    }

    public function collectionItems(): HasMany
    {
        return $this->hasMany(PlayerCollectionItem::class, 'player_user_id');
    }

    public function binders(): HasMany
    {
        return $this->hasMany(PlayerBinder::class, 'player_user_id');
    }
}
