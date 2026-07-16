<?php

namespace App\Http\Controllers;

use App\Models\Admin\Notificacion;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Centro de notificaciones compartido por los roles de staff (docente,
 * coordinador, secretaría, SystemAdministrador). El estudiante tiene su
 * propio equivalente en StudentPortalController::notificaciones(), separado
 * porque vive bajo el prefijo/rol 'estudiante'.
 *
 * El modelo Notificacion ya es genérico (solo depende de user_id), así que
 * cualquier notificación creada para un docente/secretaria/etc. (por ejemplo
 * al revisar un sílabo o una justificación) se lee aquí.
 */
class NotificacionController extends Controller
{
    public function index(): Response
    {
        $notificaciones = Notificacion::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return Inertia::render('Notificaciones/Index', [
            'notificaciones' => $notificaciones,
        ]);
    }

    public function markRead()
    {
        Notificacion::where('user_id', Auth::id())
            ->where('leido', false)
            ->update(['leido' => true]);

        return redirect()->back()->with('success', 'Notificaciones marcadas como leídas.');
    }

    /**
     * JSON liviano para la campanita del header (AppSidebarHeader.vue): a
     * diferencia de index(), no renderiza una página Inertia completa, solo
     * las últimas notificaciones para mostrarlas en el AlertDialog.
     */
    public function recientes()
    {
        $notificaciones = Notificacion::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'titulo', 'mensaje', 'leido', 'created_at']);

        return response()->json([
            'notificaciones' => $notificaciones,
            'unread_count' => $notificaciones->where('leido', false)->count(),
        ]);
    }
}
