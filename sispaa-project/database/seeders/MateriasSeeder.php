<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MateriasSeeder extends Seeder
{
    public function run(): void
    {

        $carreras = [
            1 => 'IA',   // Ingeniería Agroindustrial
            2 => 'AGN',  // Agronegocios
            3 => 'AGP',  // Ingeniería Agropecuaria
        ];

        // MATERIAS POR CARRERA
        $materiasPorCarrera = [

            // INGENIERIA AGROINDUSTRIAL
            1 => [

                // NIVEL 1
                ['QUÍMICA INORGÁNICA', 'AGRIND-101', 1],
                ['INTRODUCCIÓN A LA AGROINDUSTRIA', 'AGRIND-104', 1],
                ['FUNDAMENTOS DE FÍSICA', 'AGRIND-103', 1],
                ['METODOLOGÍA DE LA INVESTIGACIÓN', 'PAM-5202', 1],
                ['CÁLCULO DIFERENCIAL', 'PAM-1202', 1],
                ['CÁTEDRA ALFARO', '9901V01-R22', 1],

                // NIVEL 2
                ['QUÍMICA ORGÁNICA', 'PAM-2501', 2],
                ['FÍSICA', 'PAM-2212.1', 2],
                ['ESTADÍSTICA', 'PAM-1209', 2],
                ['CÁLCULO INTEGRAL', 'PAM-1202-1', 2],
                ['BIOLOGÍA', 'PAM-2302', 2],
                ['ECONOMÍA GLOBAL', '9901V02-R22', 2],

                // NIVEL 3
                ['QUÍMICA ANALÍTICA', 'AGRIND-303', 3],
                ['TERMODINÁMICA', 'AGRIND-304', 3],
                ['DISEÑO EXPERIMENTAL', 'AGRIND-405', 3],
                ['ECUACIONES DIFERENCIALES', 'PAM-1219', 3],
                ['MICROBIOLOGÍA', 'PAM-2414', 3],
                ['INNOVACIÓN, EMPRENDIMIENTO Y LIDERAZGO', '9901V03-R22', 3],

                // NIVEL 4
                ['ANÁLISIS INSTRUMENTAL', 'AGRIND-305', 4],
                ['CÁLCULOS DE INGENIERÍA', 'AGRIND-501', 4],
                ['INVESTIGACIÓN OPERATIVA', 'AGRIND-505', 4],
                ['BROMATOLOGÍA', 'AGRIND-401', 4],
                ['BIOTECNOLOGÍA', 'AGRIND-502', 4],
                ['QUÍMICA DE LOS ALIMENTOS', 'AGRIND-401A', 4],

                // NIVEL 5
                ['NUTRICIÓN', 'AGRIND-402', 5],
                ['TRANSFERENCIA DE CALOR', 'AGRIND-701', 5],
                ['ANÁLISIS SENSORIAL', 'AGRIND-803A', 5],
                ['CONTABILIDAD', 'AGRIND-406', 5],
                ['POSCOSECHA', 'AGRIND-504', 5],
                ['INDUSTRIA DE FRUTAS Y HORTALIZAS', 'AGRIND-503', 5],

                // NIVEL 6
                ['MECÁNICA DE FLUIDOS', 'AGRIND-601', 6],
                ['INDUSTRIAS CÁRNICAS', 'AGRIND-707', 6],
                ['INDUSTRIAS DE GRASAS Y ACEITES', 'AGRIND-607', 6],
                ['OPERACIONES UNITARIAS', 'AGRIND-801', 6],
                ['GESTIÓN DE LA PRODUCCIÓN', 'AGRIND-506', 6],
                ['INDUSTRIAS DE HARINAS Y BALANCEADOS', 'AGRIND-603', 6],

                // NIVEL 7
                ['INDUSTRIAS NO ALIMENTARIAS', 'AGRIND-604', 7],
                ['INDUSTRIAS PESQUERAS Y ACUÍCOLAS', 'AGRIND-806', 7],
                ['INGENIERÍA DE PROCESOS', 'AGRIND-901', 7],
                ['DISEÑO DE PLANTAS AGROINDUSTRIALES', 'AGRIND-704', 7],
                ['INDUSTRIAS LÁCTEAS', 'AGRIND-703', 7],
                ['MERCADEO Y COMERCIALIZACIÓN', 'AGRIND-606', 7],

                // NIVEL 8
                ['PROYECTOS AGROINDUSTRIALES', 'AGRIND-605', 8],
                ['APROVECHAMIENTO DE SUBPRODUCTOS AGROINDUSTRIALES', 'AGRIND-904', 8],
                ['MANTENIMIENTO Y SEGURIDAD INDUSTRIAL', 'AGRIND-708', 8],
                ['ENVASES Y EMBALAJES', 'AGRIND-903', 8],
                ['TIC – FASE DISEÑO', 'PAM-6301.1', 8],
                ['PRÁCTICAS PREPROFESIONALES', 'PRAC-001', 8],

                // NIVEL 9
                ['MARCO LEGAL AGROINDUSTRIAL', 'AGRIND-1003', 9],
                ['INVESTIGACIÓN Y DESARROLLO DE PRODUCTOS AGROINDUSTRIALES', 'AGRIND-1007', 9],
                ['GESTIÓN DE LA CALIDAD', 'AGRIND-804A', 9],
                ['TIC – FASE DE RESULTADOS E INFORME', 'PAM-6301.2', 9],
                ['PRÁCTICAS PREPROFESIONALES II', 'PRAC-002', 9],
            ],

                // AGRONEGOCIOS
            2 => [
                // NIVEL 1
                ['BIOLOGÍA','AGRN-101', 1],
                ['MATEMÁTICA', 'AGRN-102', 1],
                ['QUÍMICA', 'AGRN-103', 1],
                ['INTRODUCCIÓN A LOS AGRONEGOCIOS', 'AGRN-104', 1],
                ['CÁTEDRA ALFARO','INST-CAAL', 1],
                ['INVESTIGACIÓN APLICADA A LOS AGRONEGOCIOS','AGRN-106', 1],

                // NIVEL 2
                ['BOTÁNICA','AGRN-201', 2],
                ['MATEMÁTICA FINANCIERA', 'AGRN-202', 2],
                ['ESTADÍSTICA', 'AGRN-203', 2],
                ['ECONOMÍA GLOBAL', 'INST-ECGL', 2],
                ['AGRICULTURA ECOLÓGICA Y ALTERNATIVA', 'AGRN-205', 2],
                ['CIENCIAS DEL SUELO', 'AGRN-206', 2],

                // NIVEL 3
                ['CONTABILIDAD AGROPECUARIA', 'AGRN-301', 3],
                ['ADMINISTRACIÓN DE LA PRODUCCIÓN', 'AGRN-302', 3],
                ['LEGISLACION Y POLÍTICA NACIONAL', 'AGRN-303', 3],
                ['SISTEMAS DE PRODUCCIÓN PECUARIO Y ACUICOLA', 'AGRN-304', 3],
                ['SISTEMAS DE PRODUCCIÓN AGRÍCOLA', 'AGRN-305', 3],
                ['OPTIMIZACIÓN DEL RIEGO', 'AGRN-306', 3],

                // NIVEL 4
                ['CONTABILIDAD FINANCIERA', 'AGRN-401', 4],
                ['TEORÍA MACRO Y MICROECONOMICA', 'AGRN-402', 4],
                ['INNOVACIÓN, EMPRENDIMIENTO Y LIDERAZGO', 'INST-INE', 4],
                ['BIOTECNOLOGIAS AGROPECUARIAS', 'AGRN-404', 4],
                ['MANEJO POSTCOSECHA', 'AGRN-405', 4],
                ['SISTEMAS DE INFORMACION GEOGRÁFICA', 'AGRN-406', 4],

                // NIVEL 5
                ['ECONOMÍA AGRÍCOLA', 'AGRN-501', 5],
                ['MODELO DE NEGOCIOS', 'AGRN-502', 5],
                ['INVESTIGACIÓN DE MERCADOS DE AGRO BIODIVERSIDAD', 'AGRN-503', 5],
                ['GERENCIA FINANCIERA', 'AGRN-504', 5],
                ['SISTEMAS AGRO INDUSTRIALES', 'AGRN-505', 5],
                ['AGRICULTURA DE PRECISIÓN', 'AGRN-506', 5],

                // NIVEL 6
                ['ECONOMÍA POPULAR Y SOLIDARIA', 'AGRN-601', 6],
                ['FORMULACIÓN Y EVALUACIÓN DE PROYECTOS', 'AGRN-602', 6],
                ['ALIMENTOS Y BIOPRODUCTOS', 'AGRN-603', 6],
                ['BIOECONOMÍA', 'AGRN-604', 6],
                ['TÉCNICAS DE NEGOCIACIÓN', 'AGRN-605', 6],
                ['DESARROLLO RURAL Y TERRITORIO', 'AGRN-606', 6],

                // NIVEL 7
                ['MERCADO DE VALORES', 'AGRN-701', 7],
                ['BIOEMPRENDIMIENTOS SOSTENIBLES', 'AGRN-702', 7],
                ['PLANIFICACIÓN ESTRATÉGICA', 'AGRN-703', 7],
                ['AGROCOMERCIO EXTERIOR', 'AGRN-704', 7],

                // NIVEL 8
                ['GESTIÓN DEL TALENTO HUMANO', 'AGRN-801', 8],
                ['MARKETING AGROALIMENTARIO', 'AGRN-802', 8],
                ['GERENCIA DE PROYECTOS', 'AGRN-803', 8],
                ['SISTEMAS INTEGRADOS DE GESTIÓN', 'AGRN-804', 8],


            ],

            // INGENIERIA AGROPECUARIA
            3 => [
                ['BIOLOGÍA', 'AGR-101', 1],
                ['QUÍMICA GENERAL', 'AGR-102', 1],
                ['MATEMÁTICA', 'AGR-103', 1],
                ['METODOLOGÍA DE LA INVESTIGACIÓN', 'AGR-104', 1],
                ['INTRODUCCIÓN A LAS CIENCIAS AGROPECUARIAS', 'AGR-105', 1],
                ['CÁTEDRA ALFARO', 'AGR-106', 1],

                // NIVEL 2
                ['BOTÁNICA GENERAL', 'AGR-201', 2],
                ['QUÍMICA ORGÁNICA', 'AGR-202', 2],
                ['FISICA', 'AGR-203', 2],
                ['MICROBIOLOGÍA', 'AGR-204', 2],
                ['ZOOLOGÍA GENERAL', 'AGR-205', 2],
                ['METEOROLOGÍA', 'AGR-206', 2],

                // NIVEL 3
                ['BOTÁNICA SISTEMÁTICA', 'AGR-301', 3],
                ['CIENCIAS DEL SUELO', 'AGR-302', 3],
                ['TOPOGRAFÍA', 'AGR-303', 3],
                ['SANIDAD VEGETAL', 'AGR-304', 3],
                ['ANATOMIA Y FISIOLOGÍA ANIMAL', 'AGR-305', 3],
                ['ECONOMÍA GLOBAL', 'AGR-306', 3],

                // NIVEL 4
                ['GENÉTICA Y FITOMEJORAMIENTO', 'AGR-401', 4],
                ['CONSTRUCCIONES AGROPECUARIAS', 'AGR-402', 4],
                ['SISTEMAS DE INFORMACIÓN GEOGRÁFICA', 'AGR-403', 4],
                ['NUTRICIÓN VEGETAL', 'AGR-404', 4],
                ['SANIDAD ANIMAL', 'AGR-405', 4],
                ['AGROECOLOGÍA DE ZONAS ARIDAS Y HUMEDAS', 'AGR-406', 4],

                // NIVEL 5
                ['INNOVACIÓN, EMPRENDIMIENTO Y LIDERAZGO', 'AGR-501', 5],
                ['ECONOMÍA AGROPECUARIA', 'AGR-502', 5],
                ['SISTEMAS DE RIEGO Y DRENAJE', 'AGR-503', 5],
                ['AGROFORESTERÍA', 'AGR-504', 5],
                ['METODOS ESTADISTICOS Y DISEÑO EXPERIMENTAL', 'AGR-505', 5],
                ['MECANIZACIÓN AGROPECUARIA', 'AGR-506', 5],

                // NIVEL 6
                ['AGRICULTURA DE PRECISIÓN', 'AGR-601', 6],
                ['FUNDAMENTOS DE LA AGRICULTURA URBANA Y PERIURBANA', 'AGR-602', 6],
                ['PASTOS Y FORRAJES', 'AGR-603', 6],
                ['SISTEMAS DE PRODUCCIÓN AGRÍCOLA: CICLO CORTO', 'AGR-604', 6],
                ['SISTEMAS DE PRODUCCIÓN AGRÍCOLA: CICLO PERENNE', 'AGR-605', 6],
                ['MANEJO POSTCOSECHA', 'AGR-606', 6],

                // NIVEL 7
                ['NUTRICIÓN ANIMAL', 'AGR-701', 7],
                ['MEJORAMIENTO ANIMAL  ', 'AGR-702', 7],
                ['SISTEMA DE PRODUCCIÓN PECUARIA RUMIANTES', 'AGR-703', 7],
                ['SISTEMA DE PRODUCCIÓN PECUARIA PORCINA', 'AGR-704', 7],
                ['SISTEMAS DE PRODUCCIÓN PECUARIA: ESPECIES MENORES', 'AGR-705', 7],
                ['SISTEMA DE PRODUCCIÓN PECUARIA: AVÍCOLA', 'AGR-706', 7],

                // NIVEL 8
                ['GESTIÓN ADMINISTRATIVA Y FINANCIERA AGROPECUARIA', 'AGR-801', 8],
                ['GESTIÓN INTEGRAL DE LA CALIDAD AGROPECUARIA', 'AGR-802', 8],
                ['DESARROLLO LOCAL', 'AGR-803', 8],
                ['BIOTECNOLOGÍA', 'AGR-804', 8],

                // NIVEL 9
                ['SOCIOLOGIA RURAL', 'AGR-901', 9],
                ['MERCADO Y COMERCIALIZACIÓN AGROPECUARIA', 'AGR-902', 9],
                ['MARCO LEGAL AGROPECUARIO', 'AGR-903', 9],
                ['DISEÑO Y EVALUACIÓN DE PROYECTOS', 'AGR-904', 9],
            ],
        ];

        DB::transaction(function () use ($carreras, $materiasPorCarrera) {
            foreach ($carreras as $carreraId => $prefijo) {

                if (DB::table('materias')->where('carrera_id', $carreraId)->exists()) {
                    continue;
                }

                foreach ($materiasPorCarrera[$carreraId] as [$nombre, $codigoBase, $nivel]) {
                    DB::table('materias')->insert([
                        'carrera_id' => $carreraId,
                        'nombre' => $nombre,
                        'codigo' => $prefijo . '-' . $codigoBase,
                        'creditos' => 3,
                        'nivel' => $nivel,
                        'activa' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        $this->command?->info('Materias cargadas');
    }
}