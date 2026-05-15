<?php

namespace App\Http\Controllers\Vinculacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Vinculacion\ActividadVinculacion; // modelo correcto

class VinculacionController extends Controller
{
    public function index()
    {
        // Listar actividades de vinculación
    }

    public function actividades()
{
    // Cuenta actividades ejecutadas
    $ejecutadas = ActividadVinculacion::where('estado', 'ejecutada')->count();

    // Cuenta actividades pendientes
    $pendientes = ActividadVinculacion::where('estado', 'pendiente')->count();

    // Envía indicadores al frontend
    return Inertia::render('Vinculacion/Actividades', [
        'ejecutadas' => $ejecutadas,
        'pendientes' => $pendientes
    ]);
}

    public function asignarDocente()
    {
        // Asignar docentes a actividades
    }

    public function empresas()
    {
        // Gestionar empresas asociadas
    }

    public function estadisticas()
    {
        // Ver estadísticas de vinculación
    }
}