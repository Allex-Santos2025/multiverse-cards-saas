<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleção de Plano - Multiverse Cards</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 960px;
        }
        .plan-card {
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease-in-out;
        }
        .plan-card:hover {
            transform: translateY(-5px);
        }
        .plan-header {
            background-color: #6366f1; /* Cor primária */
            color: white;
            padding: 1.5rem;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
        }
        .plan-price {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .plan-features {
            padding: 1.5rem;
            color: #4b5563;
        }
        .plan-features ul li {
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
        }
        .plan-features ul li svg {
            margin-right: 0.5rem;
            color: #10b981; /* Cor de sucesso */
        }
        .btn-select {
            background-color: #10b981; /* Cor de sucesso */
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
            transition: background-color 0.2s;
            width: 100%;
        }
        .btn-select:hover {
            background-color: #059669;
        }
        .btn-selected {
            background-color: #9ca3af; /* Cinza para plano selecionado */
            cursor: not-allowed;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="container mx-auto p-8">
        <h2 class="text-4xl font-bold text-center text-gray-800 mb-4">Escolha seu Plano</h2>
        <p class="text-xl text-center text-gray-600 mb-12">Comece a vender suas cartas hoje mesmo!</p>

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach ($plans as $plan)
                <div class="plan-card flex flex-col">
                    <div class="plan-header text-center">
                        <h3 class="text-2xl font-bold mb-2">{{ $plan->name }}</h3>
                        <p class="text-sm opacity-90">{{ $plan->description }}</p>
                        <div class="plan-price mt-4">
                            R$ {{ number_format($plan->price, 2, ',', '.') }}<span class="text-lg font-normal">/mês</span>
                        </div>
                    </div>
                    <div class="plan-features flex-grow">
                        <ul class="list-none p-0">
                            <li>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                {{ $plan->max_products == 999999 ? 'Produtos Ilimitados' : $plan->max_products . ' Produtos' }}
                            </li>
                            <li>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                {{ $plan->transaction_fee * 100 }}% Taxa de Transação
                            </li>
                            <li>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                Suporte {{ $plan->support_level }}
                            </li>
                            @if($plan->name == 'Premium')
                                <li>
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    Recursos Avançados
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="p-4 border-t border-gray-200">
                        <form action="{{ route('onboarding.plans.select') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button type="submit" class="btn-select">Selecionar Plano</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>
