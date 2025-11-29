<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use App\Models\Set; 

class Game extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'publisher',
        'api_url',
        'formats_list', // << CORRIGIDO: Deve ser 'formats_list' (nome da coluna no DB)
        'ingestor_class', // << ADICIONADO: Para o dispatcher dinâmico
        'rate_limit_ms',  // << ADICIONADO: Para o controle de limite de taxa
        'is_active',
        'url_slug',
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'rate_limit_ms' => 'integer',
        'formats_list' => 'json', // << ADICIONADO: Para tratar o campo JSON corretamente
    ];

    /**
     * Define o relacionamento: Um Jogo TEM MUITAS Coleções (Sets).
     */
    public function sets(): HasMany
    {
        return $this->hasMany(Set::class);
    }
}