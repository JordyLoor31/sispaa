<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileUpdated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('estudiante') && $user->needs_profile_update) {
            $route = $request->route();
            $routeName = $route ? $route->getName() : null;

            $allowedRoutes = [
                'student.perfil.requerido',
                'student.perfil.edit',
                'student.perfil.update',
                'student.familiares.store',
                'student.familiares.update',
                'student.familiares.destroy',
            ];

            $isAllowed = in_array($routeName, $allowedRoutes);

            if (!$isAllowed) {
                return redirect()->route('student.perfil.requerido');
            }
        }

        return $next($request);
    }
}
