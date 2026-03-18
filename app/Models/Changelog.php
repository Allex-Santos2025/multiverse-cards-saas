<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Changelog extends Model
{
    protected $fillable = [
        'title', 'slug', 'version', 'category', 'summary', 'content', 'is_published', 'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    // Gera o slug automaticamente se você não passar um
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($changelog) {
            if (!$changelog->slug) {
                $changelog->slug = Str::slug($changelog->title);
            }
        });
    }
    public function reads(): HasMany
    {
        return $this->hasMany(ChangelogUserRead::class);
    }
    public function scopeParaLojaAtual($query)
    {
        $user = auth('store_user')->user();
        if ($user) {
            // Agora compara data, hora, minuto e segundo (rigoroso)
            return $query->where('published_at', '>=', $user->created_at);
        }
        return $query;
    }
}
