<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerAddress extends Model
{
    // Libera as colunas para o formulário salvar
    protected $fillable = [
        'player_user_id', 
        'title', 
        'receiver_name', 
        'zip_code', 
        'street', 
        'number', 
        'complement', 
        'neighborhood', 
        'city', 
        'state', 
        'is_official'
    ];

    // Ensina o caminho de volta para o Player
    public function player()
    {
        return $this->belongsTo(PlayerUser::class, 'player_user_id');
    }
}