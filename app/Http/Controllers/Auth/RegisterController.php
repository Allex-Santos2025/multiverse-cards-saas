<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Auth; 
use Illuminate\Validation\ValidationException;
use App\Models\PlayerUser; 
use App\Models\StoreUser; 
use App\Models\Store; 
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    // Métodos para retornar as views de escolha
    public function showRegistrationTypeForm()
    {
        return view('auth.register-type');
    }

    public function showPlayerRegistrationForm()
    {
        return view('auth.register-player');
    }
    
    public function showStoreRegistrationForm()
    {
        return view('auth.register-store');
    }

    /**
     * Lógica de Cadastro para o Player (Cliente).
     */
    public function registerPlayer(Request $request)
    {
        // 1. VALIDAÇÃO RIGOROSA (Alinhada com os nomes do Blade)
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'surname' => 'required|string|max:100', // Campo que faltava na validação
            'login' => 'required|string|max:100|unique:player_users,login',
            'email' => 'required|string|email|unique:player_users,email',
            'password' => 'required|string|min:8|confirmed',
            'document_number' => 'nullable|string|max:20|unique:player_users,document_number', // CPF/CNPJ
            'id_document_number' => 'nullable|string|max:20|unique:player_users,id_document_number', // RG/ID
            'birth_date' => 'nullable|date',
            'phone_number' => 'nullable|string|max:20',
        ]);

        // 2. Criação do PlayerUser
        $playerUser = PlayerUser::create([
            'name' => $validated['name'],
            'surname' => $validated['surname'],
            'login' => $validated['login'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'document_number' => $validated['document_number'],
            'id_document_number' => $validated['id_document_number'],
            'birth_date' => $validated['birth_date'],
            'phone_number' => $validated['phone_number'],
            'is_active' => true,
        ]);
        
        // 3. Autenticação (Usando o Guard 'player')
        Auth::guard('player')->login($playerUser);
        
        return redirect()->route('marketplace.home')->with('success', 'Cadastro de Jogador realizado com sucesso!');
    }
    
    /**
     * Lógica de Cadastro Atômico para Lojista e Loja.
     */
    public function registerStore(Request $request)
    {
        // 1. VALIDAÇÃO RIGOROSA (Alinhada com os nomes do Blade)
        $validated = $request->validate([
            // Dados do Proprietário (StoreUser)
            'owner_name' => 'required|string|max:100',
            'owner_surname' => 'required|string|max:100', 
            'owner_login' => 'required|string|max:100|unique:store_users,login',
            'owner_email' => 'required|string|email|unique:store_users,email',
            'password' => 'required|string|min:8|confirmed',
            'owner_document' => 'required|string|max:20|unique:store_users,document_number',
            'owner_phone' => 'nullable|string|max:20',

            // Dados da Loja (Store)
            'store_name' => 'required|string|max:255|unique:stores,name',
            'store_slug' => 'required|string|max:255|unique:stores,url_slug',
            'store_zip' => 'required|string|max:10',
            'store_state' => 'required|string|max:2',
        ]);

        // 2. CRIAÇÃO ATÔMICA
        try {
            DB::transaction(function () use ($validated, $request) {
                
                // A. Criação da Store (Loja)
                $store = Store::create([
                    'name' => $validated['store_name'],
                    'url_slug' => $validated['store_slug'],
                    'store_zip_code' => $validated['store_zip'],
                    'store_state_code' => $validated['store_state'],
                    'is_active' => true,
                ]);

                // B. Criação do StoreUser (Lojista)
                $storeUser = StoreUser::create([
                    'name' => $validated['owner_name'],
                    'surname' => $validated['owner_surname'],
                    'login' => $validated['owner_login'],
                    'email' => $validated['owner_email'],
                    'password' => Hash::make($validated['password']),
                    'document_number' => $validated['owner_document'],
                    'phone_number' => $validated['owner_phone'],
                    'is_owner' => true,
                    'is_active' => true,
                ]);
                
                // C. Vinculo Duplo CRÍTICO:
                // 1. Vinculo Reverso da Loja para o StoreUser
                $store->owner_user_id = $storeUser->id; 
                $store->save(); 
                
                // 2. Vinculo do StoreUser para a Loja
                $storeUser->current_store_id = $store->id;
                $storeUser->save(); 
            });
            
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['general' => 'Falha crítica na criação.']);
        }
        
        // 3. Autenticação (Usando o Guard 'store_user')
        $newStoreUser = StoreUser::where('email', $validated['owner_email'])->first();
        Auth::guard('store_user')->login($newStoreUser); 
        
        return redirect()->route('marketplace.home')->with('success', 'Conta de Lojista criada com sucesso!');
    }
}