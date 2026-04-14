<?php

   namespace App\Http\Middleware;

   use Closure;
   use Illuminate\Http\Request;

   class AuthenticateStoreUserOptional
   {
       public function handle(Request $request, Closure $next)
       {
           // Isso força o Laravel a carregar a sessão do guard store_user,
           // se existir cookie de sessão válido.
           auth('store_user')->user();

           return $next($request);
       }
   }