<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;
use Illuminate\Support\Facades\DB; // Importe o Facade DB

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desativa as verificações de chave estrangeira
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpa a tabela antes de inserir novos dados
        Plan::truncate();

        $plans = [
            [
                'name' => 'Básico',
                'slug' => 'basico',
                'description' => 'Ideal para quem está começando a vender online.',
                'price' => 79.90, // Preço mensal
                'billing_cycle' => 'monthly',
                'features' => json_encode([
                    'max_products' => 100,
                    'custom_domain' => false,
                    'support_level' => 'email',
                    'storage_gb' => 1,
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Para lojistas que buscam mais recursos e crescimento.',
                'price' => 99.90, // Preço mensal
                'billing_cycle' => 'monthly',
                'features' => json_encode([
                    'max_products' => 500,
                    'custom_domain' => true,
                    'support_level' => 'chat',
                    'storage_gb' => 5,
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Solução completa para grandes volumes de vendas e personalização total.',
                'price' => 119.90, // Preço mensal
                'billing_cycle' => 'monthly',
                'features' => json_encode([
                    'max_products' => 99999, // Ilimitado
                    'custom_domain' => true,
                    'support_level' => 'phone',
                    'storage_gb' => 20,
                ]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($plans as $planData) {
            Plan::create($planData);
        }

        // Reativa as verificações de chave estrangeira
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
