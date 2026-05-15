<?php

namespace App\Http\Controllers\Estudiantes;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class EstudianteController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Estudiantes/Index');
    }

    public function matriculados(): Response
    {
        return Inertia::render('Estudiantes/Matriculados');
    }

    public function faltas(): Response
    {
        return Inertia::render('Estudiantes/Faltas');
    }

    public function justificaciones(): Response
    {
        return Inertia::render('Estudiantes/Justificaciones');
    }
}
