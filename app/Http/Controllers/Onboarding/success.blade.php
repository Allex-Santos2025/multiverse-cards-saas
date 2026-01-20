<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Onboarding Concluído - Multiverse Cards</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 600px;
        }
        .success-icon {
            color: #10b981; /* Cor de sucesso */
        }
        .btn-primary {
            background-color: #6366f1;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn-primary:hover {
            background-color: #4f46e5;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="container bg-white p-8 rounded-lg shadow-lg text-center">
        <div class="success-icon mx-auto mb-6">
            <svg class="w-20 h-20 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Parabéns, Onboarding Concluído!</h2>
        <p class="text-lg text-gray-600 mb-8">Sua loja foi configurada com sucesso e seu plano de assinatura está ativo.</p>

        <a href="{{ route('dashboard.index') }}" class="btn-primary">Ir para o Dashboard da Loja</a>
    </div>
</body>
</html>
