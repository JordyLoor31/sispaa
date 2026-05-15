<?php

namespace App\Http\Controllers\Laboratorio;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class LaboratorioController extends Controller
{
    public function index()
    {
        return Inertia::render('Laboratorio/Index');
    }

    public function practicas()
    {
        return Inertia::render('Laboratorio/Practicas');
    }

    public function porCarrera()
    {
        return Inertia::render('Laboratorio/PorCarrera');
    }

    public function ubicaciones()
    {
        return Inertia::render('Laboratorio/Ubicaciones');
    }

    public function create()
    {
        return Inertia::render('Laboratorio/Create');
    }
}