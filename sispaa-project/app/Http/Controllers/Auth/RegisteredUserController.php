<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin\Notificacion;
use App\Models\User;
use App\Rules\CedulaEcuatoriana;
use App\Rules\CorreoInstitucional;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombres' => 'required|string|max:120',
            'apellidos' => 'required|string|max:120',
            'cedula' => ['required', 'digits:10', new CedulaEcuatoriana, 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new CorreoInstitucional, 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'cedula.digits' => 'La cédula debe tener exactamente 10 dígitos.',
            'cedula.unique' => 'Esta cédula ya está registrada.',
            'email.unique' => 'Este correo ya está registrado.',
        ]);

        $user = User::create([
            // users.name sigue siendo una sola columna: se arma aquí a partir
            // de los dos campos separados del formulario (nombres + apellidos).
            'name' => trim($request->nombres) . ' ' . trim($request->apellidos),
            'cedula' => $request->cedula,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        // Todo registro público es un ESTUDIANTE (igual que en el sistema origen:
        // no existe un formulario público para crear Docente/Coordinador/Secretaría/
        // SystemAdministrador; esos roles solo se asignan desde Administración).
        $user->assignRole('estudiante');

        Notificacion::create([
            'user_id' => $user->id,
            'titulo' => '¡Bienvenido/a a SISPAA!',
            'mensaje' => "Hola {$user->name}, tu cuenta fue creada correctamente. Ya puedes revisar tus documentos, matrículas y notificaciones desde tu panel de estudiante.",
            'leido' => false,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
