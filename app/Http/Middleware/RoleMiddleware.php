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
     * Supports multiple roles separated by commas, e.g. role:admin,hr
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $userRole = $request->user()->role;

        // Support multiple roles passed as separate middleware arguments
        // e.g. ->middleware('role:admin,hr')  OR  ->middleware('role:admin', 'role:hr')
        foreach ($roles as $role) {
            // Support comma-separated roles in a single argument e.g. 'admin,hr'
            $allowed = array_map('trim', explode(',', $role));
            if (in_array($userRole, $allowed, true)) {
                return $next($request);
            }
        }

        return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengakses halaman ini!');
    }
}
