<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Lojista - Multiverse Cards</title>
    <!-- Inclua seus estilos CSS aqui, por exemplo, Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Estilos personalizados para o layout, inspirados no seu HTML */
        body {
            background-color: #f3f4f6; /* Cor de fundo suave */
            font-family: 'Arial', sans-serif; /* Fonte genérica, ajuste se tiver uma específica */
        }
        .container {
            max-width: 600px; /* Ajustado para ser mais compacto como um modal */
        }
        .form-group label {
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem; /* Texto menor para labels */
        }
        .form-control {
            border-color: #d1d5db;
            border-radius: 0.375rem;
            padding: 0.75rem 1rem;
            width: 100%;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.5);
            outline: none;
        }
        .btn-primary {
            background-color: #6366f1; /* Cor primária do botão */
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: 600;
            transition: background-color 0.2s;
            text-transform: uppercase; /* Como no seu HTML */
        }
        .btn-primary:hover {
            background-color: #4f46e5;
        }
        .error-message {
            color: #ef4444;
            font-size: 0.75rem; /* Texto de erro menor */
            margin-top: 0.25rem;
        }
        .back-link {
            color: #6366f1;
            font-size: 0.875rem;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="container bg-white p-8 rounded-lg shadow-lg">
        <div class="flex items-center mb-6">
            <a href="#" class="back-link mr-4">← Voltar</a>
            <h2 class="text-2xl font-bold text-gray-800">Parceiro Versus</h2>
        </div>

        <h3 class="text-lg font-semibold text-gray-700 mb-6">Passo 1: Seus dados de acesso administrativo.</h3>

        <form method="POST" action="{{ route('merchant.register') }}">
            @csrf

            <!-- Dados Pessoais do Lojista -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="form-group">
                    <label for="name" class="block mb-1">Nome Completo</label>
                    <input type="text" id="name" name="name" class="form-control @error('name') border-red-500 @enderror" value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="surname" class="block mb-1">Sobrenome</label>
                    <input type="text" id="surname" name="surname" class="form-control @error('surname') border-red-500 @enderror" value="{{ old('surname') }}" required>
                    @error('surname')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="document_number" class="block mb-1">CPF / CNPJ</label>
                    <input type="text" id="document_number" name="document_number" class="form-control @error('document_number') border-red-500 @enderror" value="{{ old('document_number') }}" required>
                    @error('document_number')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone_number" class="block mb-1">Celular</label>
                    <input type="text" id="phone_number" name="phone_number" class="form-control @error('phone_number') border-red-500 @enderror" value="{{ old('phone_number') }}" required>
                    @error('phone_number')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="block mb-1">E-mail Comercial</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') border-red-500 @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="block mb-1">Senha</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') border-red-500 @enderror" required autocomplete="new-password">
                    @error('password')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group md:col-span-2">
                    <label for="password_confirmation" class="block mb-1">Confirmar Senha</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
            </div>

            <!-- Dados da Loja (Prévia) -->
            <h4 class="text-lg font-semibold text-gray-700 mb-4">Dados da Loja (Prévia)</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="form-group">
                    <label for="store_name" class="block mb-1">Nome Fantasia da Loja</label>
                    <input type="text" id="store_name" name="store_name" class="form-control @error('store_name') border-red-500 @enderror" value="{{ old('store_name') }}" required>
                    @error('store_name')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="url_slug" class="block mb-1">URL da Loja (Slug)</label>
                    <div class="flex items-center">
                        <span class="text-gray-500 text-sm mr-2">versus.com/</span>
                        <input type="text" id="url_slug" name="url_slug" class="form-control @error('url_slug') border-red-500 @enderror" value="{{ old('url_slug') }}" required>
                    </div>
                    @error('url_slug')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group md:col-span-2">
                    <label for="slogan" class="block mb-1">Slogan da Loja (Opcional)</label>
                    <input type="text" id="slogan" name="slogan" class="form-control @error('slogan') border-red-500 @enderror" value="{{ old('slogan') }}">
                    @error('slogan')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end mt-6">
                <button type="submit" class="btn-primary">CONTINUAR PARA VALIDAÇÃO</button>
            </div>
        </form>

        <p class="text-center text-sm text-gray-600 mt-6">
            Já tem uma conta? <a href="{{ route('merchant.login') }}" class="text-indigo-600 hover:text-indigo-800">Fazer Login</a>
        </p>
    </div>
</body>
</html>
