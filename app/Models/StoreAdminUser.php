<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreAdminUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'store_admin_users'; // Tabela correta (migrada via SQL)

    protected $fillable = [
        'store_id', // FK para Store (Pode ser NULLABLE)
        'name',
        'surname',
        'login',
        'email',
        'password',
        'is_active',
        'phone_number',
        'permissions_json',
        'hired_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions_json' => 'array',
        'hired_date' => 'date',
    ];

    public function store(): BelongsTo
    {
        // Relacionamento com a loja que ele administra
        return $this->belongsTo(Store::class);
    }
}
