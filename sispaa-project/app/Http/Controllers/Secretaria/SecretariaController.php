<?php

namespace App\Http\Controllers\Secretaria;

use App\Http\Controllers\Controller;

/**
 * DEPRECADO: todos los métodos de este controlador fueron distribuidos en
 * controladores dedicados por sub-módulo, siguiendo el patrón
 * Index/Create/Edit/Show (adaptado según el caso: colas de revisión,
 * CRUD parcial, etc.):
 *
 *   - Expediente SGA        -> Secretaria\ExpedienteController
 *   - Justificaciones       -> Secretaria\JustificacionController
 *   - Matrículas             -> Secretaria\MatriculaController
 *   - Revisión de Sílabos    -> Secretaria\SilaboRevisionController
 *   - Convocatorias          -> Secretaria\ConvocatoriaController
 *   - Grupos de Documentos   -> Secretaria\GrupoDocumentoController
 *   - Notificaciones Masivas -> Secretaria\NotificacionMasivaController
 *
 * Esta clase ya no está referenciada por ninguna ruta. Se deja vacía en vez
 * de borrarla porque el entorno de esta sesión no tiene permiso para
 * eliminar archivos del proyecto. Puede borrarse manualmente sin riesgo.
 */
class SecretariaController extends Controller
{
}
