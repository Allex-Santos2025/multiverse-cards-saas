const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css', [
       require('tailwindcss'),
       require('autoprefixer'),
   ])
   .version(); 
mix.copy('node_modules/keyrune/fonts', 'public/fonts')
   .copy('node_modules/keyrune/css/keyrune.min.css', 'public/css');