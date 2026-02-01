<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // Permitimos que todos os campos sejam preenchidos (Mass Assignment)
    protected $guarded = [];
    
    // Garantimos que o campo published_at seja tratado como data
    protected $casts = [
        'published_at' => 'datetime',
    ];
}