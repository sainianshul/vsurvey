<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (!auth()->guard('web')->check()) {
            return redirect('/login');
        }

        if (auth()->guard('web')->user()->status !== User::STATUS_ACTIVE) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403, 'Your account is not active. Please contact the administrator.');
        }

        if (auth()->guard('web')->user()->role !== User::ROLE_ADMIN) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            abort(403, 'Unauthorized Access');
        }

        return $next($request);
    }
}
