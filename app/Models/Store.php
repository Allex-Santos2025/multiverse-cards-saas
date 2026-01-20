<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Adicionado para o relacionamento BelongsTo

class Store extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'name',
        'url_slug',
        'slogan',
        'owner_user_id', // Corrigido de 'user_id' para 'owner_user_id' conforme seu schema
        'purchase_margin_cash',
        'purchase_margin_credit',
        'max_loyalty_discount',
        'pix_discount_rate',
        'store_zip_code',
        'store_state_code',
        'is_active',
        'is_template', // Chave de modelo
        // NOVOS CAMPOS ADICIONADOS PELA MIGRATION add_subscription_and_design_fields_to_stores_table
        'subscription_id',
        'logo_path',
        'primary_color',
        'secondary_color',
        'banner_path',
    ];

    /**
     * Conversão de tipos de dados.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_template' => 'boolean',
        'purchase_margin_credit' => 'decimal:3',
        'purchase_margin_cash' => 'decimal:3',
        'max_loyalty_discount' => 'decimal:3',
        'pix_discount_rate' => 'decimal:3',
    ];

    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }

    // Relacionamento com o StoreUser que é o proprietário da loja
    public function owner(): BelongsTo
    {
        return $this->belongsTo(StoreUser::class, 'owner_user_id');
    }

    // Relacionamento com a assinatura (Subscription)
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    // O relacionamento 'users()' que você tinha provavelmente se refere a 'owner_user_id'
    // Se 'users' se refere a outros tipos de usuários ou a uma relação muitos-para-muitos,
    // precisaremos de mais contexto. Por enquanto, o 'owner()' é o mais direto.
    // Se você tem outros usuários associados à loja que não são o 'owner',
    // por favor, me diga como essa relação é definida no banco de dados.
    // public function users(): HasMany
    // {
    //     return $this->hasMany(User::class); // Este 'User' precisaria ser o StoreUser ou outro tipo de usuário
    // }
}
