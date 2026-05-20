<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDashboardAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Only admin and teacher can access dashboard
        if (!$user->canAccessDashboard()) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'You do not have access to the dashboard.');
        }

        return $next($request);
    }
}
