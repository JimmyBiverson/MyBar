<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->role?->name || !in_array($user->role->name, $roles)) {
            abort(403, 'Unauthorized — insufficient role permissions.');
        }

        return $next($request);
    }
}
