<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesAndSuperAdminSeeder extends Seeder
{
    /**
     * Define os papéis e o usuário Super Admin (Root Supremo).
     */
    public function run(): void
    {
        $this->command->info('Criando Papéis e Usuário Super Admin...');
        
        // 1. Criação dos Papéis (Roles)
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'platform_staff']); 
        Role::firstOrCreate(['name' => 'store_admin']);    
        Role::firstOrCreate(['name' => 'store_staff']);    
        Role::firstOrCreate(['name' => 'customer']);       

        // 2. Criação ou Busca do Usuário Super Admin
        $superAdminUser = User::firstOrCreate(
            ['email' => 'amalgama2117@gmail.com'],
            [
                'name' => 'Allex-Santos',
                // Senha segura
                'password' => Hash::make('Admin2026'), 
                'email_verified_at' => now(),
                'store_id' => null, 
                'is_protected' => true, // Campo para proteção contra exclusão
            ]
        );
        
        // 3. Atribui o Papel
        $superAdminUser->assignRole('super_admin');
        
        $this->command->info('Papéis e Usuário Super Admin criados com sucesso!');
    }
}
