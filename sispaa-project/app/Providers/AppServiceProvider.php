<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // RBAC: 'SystemAdministrador' es el rol raíz del sistema. Con este bypass
        // estándar de Spatie, cualquier Gate::allows()/@can/middleware 'permission:'
        // devuelve true automáticamente para este rol, sin necesidad de listar
        // manualmente cada permiso existente o futuro.
        Gate::before(function ($user, string $ability) {
            return $user->hasRole('SystemAdministrador') ? true : null;
        });
    }
}
