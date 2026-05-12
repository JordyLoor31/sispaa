<?php

namespace App\Http\Controllers\Documentos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentoEstudianController extends Controller
{
    public function index()
    {
        // Listar documentos de estudiantes
    }

    public function upload(Request $request)
    {
        // Subir documento
    }

    public function review($id)
    {
        // Revisar documento
    }

    public function approve($id)
    {
        // Aprobar documento
    }

    public function reject(Request $request, $id)
    {
        // Rechazar documento
    }
}
