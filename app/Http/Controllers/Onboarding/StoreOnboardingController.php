<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Store; // Importado (para atualizar a store com subscription_id)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoreOnboardingController extends Controller
{
    // Exibe a tela de seleção de planos
    public function showPlanSelection()
    {
        $plans = Plan::where('is_active', true)->get();
        return view('onboarding.plan-selection', compact('plans'));
    }

    // Processa a seleção do plano (simula checkout)
    public function selectPlan(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $storeUser = Auth::guard('store_user')->user();

        // Verifica se o lojista já tem uma assinatura ativa ou pendente
        if ($storeUser->subscriptions()->whereIn('status', ['active', 'pending'])->exists()) {
            throw ValidationException::withMessages([
                'plan_id' => 'Você já possui uma assinatura ativa ou pendente.',
            ]);
        }

        // Pega a loja recém-criada (deve existir, pois foi criada no registro)
        $store = $storeUser->currentStore; // Ou $storeUser->stores()->first();

        if (!$store) {
            // Isso não deveria acontecer se o fluxo for seguido corretamente
            return redirect()->route('merchant.register')->with('error', 'Sua loja não foi encontrada. Por favor, registre-se novamente.');
        }

        // Simulação de Checkout:
        // Aqui, em um sistema real, você integraria com um gateway de pagamento.
        // Para o MVP, vamos criar a assinatura diretamente.
        $subscription = Subscription::create([
            'store_user_id' => $storeUser->id,
            'plan_id' => $plan->id,
            'status' => 'active', // Para o MVP, ativamos diretamente
            'amount' => $plan->price,
            'payment_method' => 'simulated', // Método de pagamento simulado
            'payment_gateway' => 'none', // Gateway de pagamento simulado
            'ends_at' => now()->addYear(), // Assinatura válida por 1 ano para o MVP
        ]);

        // Vincula a assinatura à loja
        $store->subscription_id = $subscription->id;
        $store->save();

        // Redireciona para a tela de sucesso do onboarding
        return redirect()->route('onboarding.success');
    }

    public function onboardingSuccess()
    {
        return view('onboarding.success'); // Sua view de sucesso do onboarding
    }
}
