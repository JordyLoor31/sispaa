<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasBreadcrumbs;
use App\Models\Admin\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

/**
 * Notificaciones Masivas, separado de SecretariaController. No es CRUD:
 * es un formulario de envío único + un historial de envíos pasados, ambos
 * en una sola pantalla. No hay una entidad individual que editar, ver o
 * eliminar, así que no aplica el patrón Index/Create/Edit/Show — solo
 * Index (que hace de formulario + historial) y Store.
 */
class NotificacionMasivaController extends Controller
{
    use HasBreadcrumbs;

    public function index(): Response
    {
        // Historial agrupado por título+mensaje+minuto de envío (igual al criterio
        // usado en el sistema Next.js origen para reconstruir "envíos masivos"
        // a partir de filas individuales de la tabla de notificaciones).
        $historial = Notificacion::selectRaw("titulo, mensaje, date_trunc('minute', created_at) as enviado_en, count(*) as total_destinatarios")
            ->groupBy('titulo', 'mensaje', 'enviado_en')
            ->orderByDesc('enviado_en')
            ->limit(50)
            ->get();

        return Inertia::render('Secretaria/NotificacionesMasivas/Index', [
            'historial' => $historial,
            'roles' => Role::pluck('name'),
            'breadcrumbs' => $this->secretariaBreadcrumbs('Notificaciones Masivas'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string|max:1000',
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|string|exists:roles,name',
        ]);

        $destinatarios = User::role($request->roles)
            ->where('id', '!=', Auth::id())
            ->get(['id']);

        $ahora = now();
        $notificaciones = $destinatarios->map(fn ($user) => [
            'user_id' => $user->id,
            'titulo' => $request->titulo,
            'mensaje' => $request->mensaje,
            'leido' => false,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ])->all();

        if (!empty($notificaciones)) {
            Notificacion::insert($notificaciones);
        }

        return redirect()->route('secretaria.notificaciones-masivas.index')
            ->with('success', 'Notificación enviada a ' . count($notificaciones) . ' usuario(s).');
    }
}
