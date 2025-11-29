<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Jogador - Multiverse Cards</title>
    <style>
        body { font-family: sans-serif; background-color: #f0f0f0; color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); max-width: 700px; width: 90%; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .form-grid-full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9em; }
        input[type="text"], input[type="email"], input[type="password"], input[type="date"] {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;
        }
        .btn-submit { background-color: #ff9900; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 1em; margin-top: 20px; transition: background-color 0.3s; }
        .btn-submit:hover { background-color: #cc7a00; }
        .error { color: #ff3333; font-size: 0.8em; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align: center; color: #ff9900; font-size: 1.8em; margin-bottom: 20px;">Cadastro de Jogador</h1>

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

        <form method="POST" action="{{ route('register.player') }}">
            @csrf

            <div class="form-grid">
                <div>
                    <label for="name">Nome</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </div>
                
                <div>
                    <label for="surname">Sobrenome</label>
                    <input type="text" id="surname" name="surname" value="{{ old('surname') }}" required>
                    @error('surname')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="login">Login (Nick)</label>
                    <input type="text" id="login" name="login" value="{{ old('login') }}" required>
                    @error('login')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="document_number">CPF/CNPJ</label>
                    <input type="text" id="document_number" name="document_number" value="{{ old('document_number') }}" placeholder="Obrigatório para segurança">
                    @error('document_number')<span class="error">{{ $message }}</span>@enderror
                </div>
                
                <div>
                    <label for="id_document_number">RG/ID (Opcional)</label>
                    <input type="text" id="id_document_number" name="id_document_number" value="{{ old('id_document_number') }}" placeholder="Para maior segurança">
                    @error('id_document_number')<span class="error">{{ $message }}</span>@enderror
                </div>
                
                <div>
                    <label for="birth_date">Data de Nascimento</label>
                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                    @error('birth_date')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="phone_number">Telefone (Celular)</label>
                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}">
                    @error('phone_number')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label for="password_confirmation">Confirmar Senha</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>

            <div class="form-grid-full">
                <button type="submit" class="btn-submit">Criar Minha Conta de Jogador</button>
            </div>
            
        </form>
    </div>
</body>
</html>