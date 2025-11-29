<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Lojista - Multiverse Cards</title>
    <style>
        body { font-family: sans-serif; background-color: #f0f0f0; color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); max-width: 900px; width: 90%; }
        h1 { text-align: center; color: #007bff; font-size: 1.8em; margin-bottom: 20px; }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 5px; margin-top: 30px; margin-bottom: 20px; font-size: 1.4em; color: #007bff; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; }
        .form-grid-2 { grid-template-columns: 1fr 1fr; }
        .form-grid-full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9em; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .btn-submit { background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 1em; margin-top: 20px; transition: background-color 0.3s; }
        .btn-submit:hover { background-color: #0056b3; }
        .error { color: #ff3333; font-size: 0.8em; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cadastro de Lojista</h1>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">Crie sua conta de Proprietário e registre sua primeira Loja.</p>

        @if ($errors->any())
            <div style="background-color: #fdd; color: #c00; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                Por favor, corrija os seguintes erros:
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <h2>1. Dados do Proprietário (Seu Cadastro Pessoal)</h2>
            <div class="form-grid form-grid-2">
                
                <div>
                    <label for="owner_name">Nome</label>
                    <input type="text" id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required>
                    @error('owner_name')<span class="error">{{ $message }}</span>@enderror
                </div>
                
                <div>
                    <label for="owner_surname">Sobrenome</label>
                    <input type="text" id="owner_surname" name="owner_surname" value="{{ old('owner_surname') }}" required>
                    @error('owner_surname')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="owner_login">Login (Nick)</label>
                    <input type="text" id="owner_login" name="owner_login" value="{{ old('owner_login') }}" required>
                    @error('owner_login')<span class="error">{{ $message }}</span>@enderror
                </div>
                
                <div>
                    <label for="owner_email">E-mail</label>
                    <input type="email" id="owner_email" name="owner_email" value="{{ old('owner_email') }}" required>
                    @error('owner_email')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="owner_document">CPF/CNPJ do Proprietário</label>
                    <input type="text" id="owner_document" name="owner_document" value="{{ old('owner_document') }}" required>
                    @error('owner_document')<span class="error">{{ $message }}</span>@enderror
                </div>
                
                <div>
                    <label for="owner_phone">Telefone (Celular)</label>
                    <input type="text" id="owner_phone" name="owner_phone" value="{{ old('owner_phone') }}">
                    @error('owner_phone')<span class="error">{{ $message }}</span>@enderror
                </div>
                
                <div class="form-grid-full">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-grid-full">
                    <label for="password_confirmation">Confirmar Senha</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>

            <h2>2. Registro da Loja (Obrigatoriamente Vinculado)</h2>
            <div class="form-grid form-grid-2">
                
                <div>
                    <label for="store_name">Nome Fantasia da Loja</label>
                    <input type="text" id="store_name" name="store_name" value="{{ old('store_name') }}" required>
                    @error('store_name')<span class="error">{{ $message }}</span>@enderror
                </div>
                
                <div>
                    <label for="store_slug">URL da Loja (Slug)</label>
                    <input type="text" id="store_slug" name="store_slug" value="{{ old('store_slug') }}" required placeholder="Ex: minha-loja">
                    @error('store_slug')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="store_zip">CEP da Loja</label>
                    <input type="text" id="store_zip" name="store_zip" value="{{ old('store_zip') }}" required>
                    @error('store_zip')<span class="error">{{ $message }}</span>@enderror
                </div>
                
                <div>
                    <label for="store_state">Estado (Sigla)</label>
                    <input type="text" id="store_state" name="store_state" value="{{ old('store_state') }}" required maxlength="2" placeholder="Ex: SP">
                    @error('store_state')<span class="error">{{ $message }}</span>@enderror
                </div>
                
            </div>
            
            <div class="form-grid-full" style="margin-top: 30px;">
                <button type="submit" class="btn-submit">Criar Conta e Minha Loja</button>
            </div>
            
        </form>
    </div>
</body>
</html>