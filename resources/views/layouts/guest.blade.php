<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Versus TCG | Painel do Lojista</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('assets/favicon.png') }}" type="image/png">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0a0a0a; color: white; overflow-x: hidden; }
        .text-gradient { background: linear-gradient(to right, #ff9900, #ff5500); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .btn-primary { background: linear-gradient(135deg, #ff9900 0%, #ff5500 100%); transition: all 0.3s ease; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(255, 85, 0, 0.4); }
        .input-dark { background-color: #0a0a0a; border: 1px solid #333; transition: all 0.3s ease; }
        .input-dark:focus { border-color: #ff5500; box-shadow: 0 0 0 2px rgba(255, 85, 0, 0.2); outline: none; }
        .glow-bg { position: absolute; width: 600px; height: 600px; background: radial-gradient(circle, rgba(255, 85, 0, 0.08) 0%, rgba(0, 0, 0, 0) 70%); top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: -1; pointer-events: none; }
    </style>
    @livewireStyles
</head>
<body class="antialiased flex flex-col min-h-screen">
    {{ $slot }}

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('ph-eye');
                eyeIcon.classList.add('ph-eye-slash');
                eyeIcon.classList.add('text-[#ff5500]');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('ph-eye-slash');
                eyeIcon.classList.add('ph-eye');
                eyeIcon.classList.remove('text-[#ff5500]');
            }
        }
    </script>
    @livewireScripts
</body>
</html>