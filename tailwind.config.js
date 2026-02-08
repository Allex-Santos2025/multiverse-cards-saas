/** @type {import('tailwindcss').Config} */
module.exports = {

    darkMode: 'class', 
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./app/Livewire/**/*.php",
    ],
    
    theme: {
        extend: {
            colors: {
                // --- CORES DA SUA PALETA VERSUS TCG ---
                'dark-body-bg': '#050505',      // Do seu app.css var(--bg-body)
                'dark-page-bg': '#0a0a0a',      // Do seu body background-color do HTML puro e basico:27
                'dark-form-bg': '#111111',      // O #111 do seu HTML puro
                'dark-input-bg': '#000000',     // O preto para inputs do seu HTML puro
                'dark-border-primary': '#1f2937', // Equivalente ao gray-800 do Tailwind
                'dark-border-secondary': '#333333', // Equivalente ao gray-700 do Tailwind
                'brand-orange': '#ff5500',      // Seu laranja principal do HTML puro
                'brand-orange-hover': '#cc4400', // Um tom mais escuro para hover
                'text-light': '#ffffff',        // Branco para textos
                'text-muted': '#9ca3af',        // Cinza para textos secundários (gray-400)
                'text-label': '#6b7280',        // Cinza para labels (gray-500)
                'error-red': '#ef4444',         // Vermelho para erros (red-500)
                'success-green': '#22c5e0',     // Verde para sucesso (green-500)
                'purple-accent': '#a855f7',     // purple-400 do seu HTML puro
                // --- FIM CORES DA SUA PALETA ---
            },
            borderRadius: {
                '2xl': '1rem', // Adicionado para corresponder ao rounded-2xl do seu HTML
            },
            animation: {
                fadeIn: 'fadeIn 0.5s ease-out forwards',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: 0, transform: 'translateY(10px)' },
                    '100%': { opacity: 1, transform: 'translateY(0)' },
                }
            }
        },
    },
    // AQUI ESTÁ A CORREÇÃO DAS BORDAS SUMIDAS
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
