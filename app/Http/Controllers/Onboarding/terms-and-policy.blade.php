<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos e Política - Multiverse Cards</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f4f6;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 800px;
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
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="container bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Termos de Uso e Política de Privacidade</h2>

        <div class="prose max-w-none mb-8 text-gray-700">
            <p>Bem-vindo ao Multiverse Cards! Para continuar, por favor, leia e aceite nossos Termos de Uso e Política de Privacidade.</p>

            <h3 class="text-xl font-semibold mt-6 mb-2">1. Termos de Uso</h3>
            <p>Ao utilizar os serviços do Multiverse Cards, você concorda em cumprir e estar vinculado aos seguintes termos e condições de uso. Estes termos aplicam-se a todos os visitantes, usuários e outros que acessam ou usam o Serviço.</p>
            <ul class="list-disc ml-6">
                <li>Você deve ter pelo menos 18 anos de idade para usar este serviço.</li>
                <li>Você é responsável por manter a segurança de sua conta e senha.</li>
                <li>Você não deve transmitir quaisquer worms ou vírus ou qualquer código de natureza destrutiva.</li>
                <li>Qualquer abuso, ameaça, difamação ou calúnia de qualquer cliente, funcionário, membro ou oficial da empresa resultará na rescisão imediata da sua conta.</li>
            </ul>

            <h3 class="text-xl font-semibold mt-6 mb-2">2. Política de Privacidade</h3>
            <p>Sua privacidade é importante para nós. Esta política de privacidade explica como coletamos, usamos, divulgamos e protegemos suas informações pessoais.</p>
            <ul class="list-disc ml-6">
                <li>Coletamos informações que você nos fornece diretamente, como nome, e-mail, CPF/CNPJ, endereço e informações de pagamento.</li>
                <li>Usamos suas informações para fornecer, manter e melhorar nossos serviços, processar transações e enviar comunicações importantes.</li>
                <li>Não compartilhamos suas informações pessoais com terceiros, exceto conforme necessário para fornecer os serviços ou conforme exigido por lei.</li>
                <li>Implementamos medidas de segurança para proteger suas informações contra acesso não autorizado, alteração, divulgação ou destruição.</li>
            </ul>

            <p class="mt-6">Ao clicar em "Aceitar e Continuar", você reconhece que leu, entendeu e concorda com os Termos de Uso e a Política de Privacidade do Multiverse Cards.</p>
        </div>

        <form method="POST" action="{{ route('onboarding.terms.accept') }}">
            @csrf
            <div class="flex items-center mb-4">
                <input type="checkbox" id="accept_terms" name="accept_terms" class="form-checkbox h-5 w-5 text-blue-600" required>
                <label for="accept_terms" class="ml-2 text-gray-700">Eu li e aceito os Termos de Uso e a Política de Privacidade.</label>
            </div>
            @error('accept_terms')
                <p class="error-message">{{ $message }}</p>
            @enderror

            <div class="flex justify-center mt-6">
                <button type="submit" class="btn-primary">Aceitar e Continuar</button>
            </div>
        </form>
    </div>
</body>
</html>
