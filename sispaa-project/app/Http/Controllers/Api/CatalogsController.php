<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CatalogsController extends Controller
{
    public function carreras()
    {
        $carreras = DB::table('carreras')->select('id', 'nombre')->where('activa', true)->orderBy('id')->get();

        return response()->json($carreras);
    }
}
