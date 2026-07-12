<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Carrera;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

/**
 * CRUD de Usuarios, separado de AdminPortalController siguiendo el patrón
 * Index/Create/Edit/Show + Form + columns.ts (ver resources/js/pages/Admin/Usuarios).
 */
class UserController extends Controller
{
    use HasBreadcrumbs;

    public function index(Request $request): Response
    {
        $query = User::with(['roles', 'carrera']);

        if ($q = $request->input('q')) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('cedula', 'like', "%{$q}%");
            });
        }

        if (($role = $request->input('role')) && $role !== 'all') {
            $query->role($role);
        }

        $perPage = max(1, min(100, (int) $request->input('per_page', 10)));

        $usuarios = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => Role::all(),
            'filters' => $request->only(['q', 'role', 'per_page']),
            'breadcrumbs' => $this->adminBreadcrumbs('Usuarios'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Usuarios/Create', [
            'roles' => Role::all(),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'breadcrumbs' => $this->adminBreadcrumbs('Usuarios', 'Nuevo Usuario', route('admin.usuarios.index')),
        ]);
    }

    /**
     * Valida el arreglo de roles a asignar, incluyendo reglas de negocio de
     * jerarquía (ej. 'coordinador' solo se puede otorgar a alguien que
     * también tenga 'docente' — es un rol adicional, no un reemplazo).
     */
    private function validateRolesAssignment(Request $request): void
    {
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|string|exists:roles,name',
        ], [
            'roles.required' => 'Selecciona al menos un rol.',
        ]);

        if (in_array('coordinador', $request->roles, true) && !in_array('docente', $request->roles, true)) {
            throw ValidationException::withMessages([
                'roles' => 'El rol Coordinador solo se puede asignar a un usuario que también tenga el rol Docente.',
            ]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'cedula' => 'nullable|string|max:10|unique:users',
            'telefono' => 'nullable|string|max:15',
            'carrera_id' => 'nullable|exists:carreras,id',
        ]);
        $this->validateRolesAssignment($request);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
            'carrera_id' => $request->carrera_id,
            'is_active' => true,
        ]);

        $user->syncRoles($request->roles);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $user): Response
    {
        $user->load(['roles', 'carrera']);

        return Inertia::render('Admin/Usuarios/Show', [
            'usuario' => $user,
            'breadcrumbs' => $this->adminBreadcrumbs('Usuarios', 'Ver Usuario', route('admin.usuarios.index'), $user->name),
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load(['roles', 'carrera']);

        return Inertia::render('Admin/Usuarios/Edit', [
            'usuario' => $user,
            'roles' => Role::all(),
            'carreras' => Carrera::orderBy('nombre')->get(['id', 'nombre']),
            'breadcrumbs' => $this->adminBreadcrumbs('Usuarios', 'Editar Usuario', route('admin.usuarios.index'), $user->name),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'cedula' => 'nullable|string|max:10|unique:users,cedula,' . $user->id,
            'telefono' => 'nullable|string|max:15',
            'carrera_id' => 'nullable|exists:carreras,id',
        ]);
        $this->validateRolesAssignment($request);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
            'carrera_id' => $request->carrera_id,
        ]);

        $user->syncRoles($request->roles);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'Estado del usuario actualizado.');
    }
}
