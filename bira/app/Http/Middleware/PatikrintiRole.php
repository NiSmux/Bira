<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PatikrintiRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
    // Patikriname, ar vartotojas yra vadybininkas (role_id = 2)
        if (auth()->check() && auth()->user()->role_id == 2) {
            return $next($request);
        }

    return redirect('/')->with('error', 'Neturite teisių.');
    }
}
