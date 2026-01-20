<?php

return [
    
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        // CORREÇÃO 1: Usamos 'users' como broker padrão para o SuperUser
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'), 
    ],
    
    // --- GUARDS (Sessões de Login) ---
    
    'guards' => [
        'web' => [ // O Guard padrão (para o SuperUser)
            'driver' => 'session',
            'provider' => 'users',
        ],

        // NOVOS GUARDS PARA O SISTEMA (Segregação de Sessão)
        'admin' => [ // Guard para o Staff do Sistema
            'driver' => 'session',
            'provider' => 'admin_users',
        ],
        'store_user' => [ // Guard para Lojistas
            'driver' => 'session',
            'provider' => 'store_users',
            
        ],
        'store_admin' => [ 
        'driver' => 'session',
        'provider' => 'store_admin_users', // Usa o provider que já existe
        ],
        'player' => [ // Guard para Clientes/Jogadores
            'driver' => 'session',
            'provider' => 'player_users',
        ],
    ],
        
    // --- PROVIDERS (Modelos de Dados) ---
    
    'providers' => [
        // 1. O Provider padrão (agora SuperUser)
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class, 
        ],

        // 2. Providers para os Novos Modelos Segregados
        'admin_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\AdminUser::class,
        ],
        'store_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\StoreUser::class,
        ],
        'store_admin_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\StoreAdminUser::class,
        ],
        'player_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\PlayerUser::class,
        ],
    ],

    // --- SENHAS (Reset de Senha) ---
    
    'passwords' => [
        // O Laravel usa este broker, que agora está definido corretamente
        'users' => [ 
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
    
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];