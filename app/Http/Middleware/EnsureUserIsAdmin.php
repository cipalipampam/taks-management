<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && ($user->hasRole('admin') || $user->can('users.manage'))) {
            return $next($request);
        }

        abort(403, 'Unauthorized access. Only administrators or users with users.manage permission can access this panel.');
    }
}
