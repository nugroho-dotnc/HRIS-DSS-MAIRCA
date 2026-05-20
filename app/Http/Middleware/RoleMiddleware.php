<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, String $role): Response
    {
        if($request->user()->role === $role){
            return $next($request);
        }
        return redirect()->back()->with('error', 'you\'re not allowed to access this page!');
    }
}
