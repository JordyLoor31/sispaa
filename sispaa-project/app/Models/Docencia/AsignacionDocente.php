<?php

namespace App\Models\Docencia;

use Illuminate\Database\Eloquent\Model;

class AsignacionDocente extends Model
{
    protected $table = 'asignaciones_docente';

    protected $fillable = [
        'docente_id',
        'materia_id',
        'periodo_id',
        'tipo_rol',
        'grupo',
    ];

    public function docente()
    {
        return $this->belongsTo(\App\Models\User::class, 'docente_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function periodo()
    {
        return $this->belongsTo(\App\Models\Admin\PeriodoAcademico::class, 'periodo_id');
    }

    /**
     * Replica las asignaciones de un periodo a otro (docente, materia,
     * tipo_rol y grupo), respetando la clave única (docente, materia,
     * periodo, grupo): las filas que ya existan en el periodo destino se
     * omiten. Se usa al activar un periodo nuevo para que los docentes ya
     * tengan sus materias asignadas y la carrera quede vinculada sin
     * reingresar todo a mano. Devuelve cuántas asignaciones se copiaron.
     */
    public static function copiarDesdePeriodo(int $desdePeriodoId, int $haciaPeriodoId): int
    {
        $origenes = self::where('periodo_id', $desdePeriodoId)->get();

        $copiadas = 0;
        foreach ($origenes as $origen) {
            $query = self::where('periodo_id', $haciaPeriodoId)
                ->where('docente_id', $origen->docente_id)
                ->where('materia_id', $origen->materia_id);

            if ($origen->grupo === null) {
                $query->whereNull('grupo');
            } else {
                $query->where('grupo', $origen->grupo);
            }

            if ($query->exists()) {
                continue;
            }

            self::create([
                'periodo_id' => $haciaPeriodoId,
                'docente_id' => $origen->docente_id,
                'materia_id' => $origen->materia_id,
                'tipo_rol' => $origen->tipo_rol,
                'grupo' => $origen->grupo,
            ]);
            $copiadas++;
        }

        return $copiadas;
    }
}
