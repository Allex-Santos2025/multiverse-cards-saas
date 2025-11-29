<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany; // Para o relacionamento com os AdminUsers

class SuperUser extends Authenticatable
{
    use HasFactory, Notifiable;

    // CRUCIAL: Aponta para a tabela renomeada
    protected $table = 'super_users'; 

    /**
     * Os campos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'store_id', 
        'is_protected', 
    ];

    /**
     * Os atributos que devem ser escondidos (segurança).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    

}
