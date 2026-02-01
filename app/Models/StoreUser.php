<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Contracts\Auth\MustVerifyEmail; // Importante: Adicionar esta linha
use App\Traits\HasEmailVerificationWizard;

class StoreUser extends Authenticatable implements MustVerifyEmail // Importante: Adicionar 'implements MustVerifyEmail'
{
    use HasFactory, Notifiable; 
    use \Illuminate\Auth\MustVerifyEmail;
    use HasEmailVerificationWizard;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'login',
        'email',
        'password',
        'is_active',
        'phone_number',
        'current_store_id',
        'document_number',
        'id_document_number',
        'social_name',
        'company_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token', // Adicionado para ocultar o remember_token
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // Adicionado para tratar como data/hora
        'password' => 'hashed', // Adicionado para hash automático no Laravel 12
        'is_active' => 'boolean', // Já existia, mas bom confirmar
    ];

    /**
     * Get the store that the user currently owns.
     */
    public function currentStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'current_store_id');
    }
    public function store(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        // O usuário TEM UMA loja onde o 'owner_user_id' é o ID dele.
        return $this->hasOne(Store::class, 'owner_user_id');
    }
    /**
     * Get the subscriptions for the store user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
