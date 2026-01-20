<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StoreUser;
use App\Models\Store; // <<-- ESTA LINHA DEVE SER ADICIONADA AQUI
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str; // <<-- ESTA LINHA DEVE SER ADICIONADA AQUI

class StoreUserRegisterController extends Controller
{
    /**
     * Exibe o formulário de registro para lojistas.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        // Certifique-se de que a view 'auth.store-register' existe.
        // Você deve ter renomeado 'merchant-register.blade.php' para 'store-register.blade.php'.
        return view('auth.store-register');
    }

    /**
     * Lida com o registro de um novo lojista e a criação da loja.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'document_number' => ['required', 'string', 'max:14'], // CPF ou CNPJ
            'phone_number' => ['required', 'string', 'max:15'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:store_users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'store_name' => ['required', 'string', 'max:255'],
            'url_slug' => ['required', 'string', 'max:255', 'unique:stores,url_slug'], // Garante slug único para a loja
            'slogan' => ['nullable', 'string', 'max:255'],
        ]);

        // 1. Cria o StoreUser
        $user = StoreUser::create([
            'name' => $validatedData['name'],
            'surname' => $validatedData['surname'],
            'document_number' => $validatedData['document_number'],
            'phone_number' => $validatedData['phone_number'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'current_store_id' => null, // Será preenchido após a criação da loja
            'is_onboarded' => false, // <<-- ESTES CAMPOS SÃO INICIALIZADOS AQUI
            'accepted_terms' => false, // <<-- ESTES CAMPOS SÃO INICIALIZADOS AQUI
            'selected_plan_id' => null, // <<-- ESTES CAMPOS SÃO INICIALIZADOS AQUI
        ]);

        // 2. Cria a Store e associa ao StoreUser
        $store = Store::create([
            'owner_user_id' => $user->id,
            'name' => $validatedData['store_name'],
            'url_slug' => Str::slug($validatedData['url_slug']), // <<-- Str::slug() É USADO AQUI
            'slogan' => $validatedData['slogan'] ?? null,
            'currency' => 'BRL', // Valor padrão, pode ser configurável
            'purchase_margins' => 0.0, // Valor padrão
            'discounts' => 0.0, // Valor padrão
            'pix_discount_rate' => 0.0, // Valor padrão
            'zip' => null, // Pode ser adicionado em uma etapa posterior do onboarding
            'state' => null, // Pode ser adicionado em uma etapa posterior do onboarding
            'is_template' => false,
            'is_active' => true, // Loja ativa por padrão após o registro
        ]);

        // 3. Atualiza o StoreUser com o ID da loja criada
        $user->current_store_id = $store->id;
        $user->save();

        // 4. Autentica o usuário recém-criado
        Auth::guard('store_user')->login($user);

        // 5. Redireciona para a próxima etapa do onboarding (aceitação dos termos)
        return redirect()->route('onboarding.terms');
    }
}
