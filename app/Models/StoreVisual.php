<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreVisual extends Model
{
    // Permite salvar os dados em massa (mass assignment)
    protected $guarded = []; 

    // Relacionamento reverso: O visual pertence a uma loja
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}