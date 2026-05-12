<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index()
    {
        // Listar notificaciones
    }

    public function markRead($id)
    {
        // Marcar notificación como leída
    }

    public function send(Request $request)
    {
        // Enviar notificación
    }
}
