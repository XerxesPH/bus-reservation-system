<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in AND is an admin
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request); // Allow access
        }

        // If not, kick them out to the home page
        return redirect('/')->with('error', 'You do not have admin access.');
    }
}
