<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoreUserLoginController extends Controller
{
    /**
     * Exibe o formulário de login para lojistas.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.store-login'); // Você precisará criar esta view
    }

    /**
     * Lida com a tentativa de login do lojista.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('store_user')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::guard('store_user')->user();

            // Redireciona para o fluxo de onboarding se o usuário ainda não estiver onboarded
            if (!$user->is_onboarded) {
                // Verifica qual etapa do onboarding precisa ser completada
                if (!$user->accepted_terms) {
                    return redirect()->route('onboarding.terms');
                }
                if (is_null($user->selected_plan_id)) {
                    return redirect()->route('onboarding.plans');
                }
                // Se chegou aqui, algo deu errado no is_onboarded, mas todas as etapas foram completadas
                // Pode ser um bom lugar para forçar is_onboarded = true e redirecionar para o dashboard
                $user->is_onboarded = true;
                $user->save();
                return redirect()->route('dashboard.index');
            }

            // Se já estiver onboarded, redireciona para o dashboard
            return redirect()->intended(route('dashboard.index'));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Lida com o logout do lojista.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('store_user')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/'); // Redireciona para a página inicial ou para a página de login
    }
}
