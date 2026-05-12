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

        $materiasPorCarrera = [

            // ─────────────────────────────────────────────────────────────
            // 1 · INGENIERÍA AGROINDUSTRIAL
            // ─────────────────────────────────────────────────────────────
            1 => [
                // [nombre, codigo, nivel]

                // NIVEL 1
                ['Química Inorgánica',                          'AGRIND-101',      1],
                ['Introducción a la Agroindustria',             'AGRIND-104',      1],
                ['Fundamentos de Física',                       'AGRIND-103',      1],
                ['Metodología de la Investigación',             'PAM-5202',        1],
                ['Cálculo Diferencial',                         'PAM-1202',        1],
                ['Cátedra Alfaro',                              '9901V01-R22',     1],

                // NIVEL 2
                ['Química Orgánica',                            'PAM-2501',        2],
                ['Física',                                      'PAM-2212.1',      2],
                ['Estadística',                                 'PAM-1209',        2],
                ['Cálculo Integral',                            'PAM-1202-1',      2],
                ['Biología',                                    'PAM-2302',        2],
                ['Economía Global',                             '9901V02-R22',     2],

                // NIVEL 3
                ['Química Analítica',                           'AGRIND-303',      3],
                ['Termodinámica',                               'AGRIND-304',      3],
                ['Diseño Experimental',                         'AGRIND-405',      3],
                ['Ecuaciones Diferenciales',                    'PAM-1219',        3],
                ['Microbiología',                               'PAM-2414',        3],
                ['Innovación, Emprendimiento y Liderazgo',      '9901V03-R22',     3],

                // NIVEL 4
                ['Análisis Instrumental',                       'AGRIND-305',      4],
                ['Cálculos de Ingeniería',                      'AGRIND-501',      4],
                ['Investigación Operativa',                     'AGRIND-505',      4],
                ['Bromatología',                                'AGRIND-401',      4],
                ['Biotecnología',                               'AGRIND-502',      4],
                ['Química de los Alimentos',                    'AGRIND-401A',     4],

                // NIVEL 5
                ['Nutrición',                                   'AGRIND-402',      5],
                ['Transferencia de Calor',                      'AGRIND-701',      5],
                ['Análisis Sensorial',                          'AGRIND-803A',     5],
                ['Contabilidad General y de Costo',             'AGRIND-406',      5], // antes: "Contabilidad"
                ['Manejo Postcosecha',                          'AGRIND-504',      5], // antes: "Poscosecha"
                ['Industria de Frutas y Hortalizas',            'AGRIND-503',      5],

                // NIVEL 6
                ['Mecánica de Fluidos',                         'AGRIND-601',      6],
                ['Industrias Cárnicas',                         'AGRIND-707',      6],
                ['Industrias de Grasas y Aceites',              'AGRIND-607',      6],
                ['Operaciones Unitarias',                       'AGRIND-801',      6],
                ['Gestión de la Producción',                    'AGRIND-506',      6],
                ['Industrias de Harinas y Balanceados',         'AGRIND-603',      6],

                // NIVEL 7
                ['Industria de Fibras no Alimentarias',         'AGRIND-604',      7], // antes: "Industrias no Alimentarias"
                ['Industrias de Curtiembre, Madera y Papel',    'AGRIND-604B',     7], // nueva
                ['Industrias Pesqueras y Acuícolas',            'AGRIND-806',      7],
                ['Ingeniería de Procesos',                      'AGRIND-901',      7],
                ['Diseño de Plantas Agroindustriales',          'AGRIND-704',      7],
                ['Industrias Lácteas',                          'AGRIND-703',      7],
                ['Mercadeo y Comercialización',                 'AGRIND-606',      7],

                // NIVEL 8
                ['Proyectos Agroindustriales',                  'AGRIND-605',      8],
                ['Aprovechamiento de Subproductos Agroindustriales', 'AGRIND-904', 8],
                ['Mantenimiento y Seguridad Industrial',        'AGRIND-708',      8],
                ['Envases y Embalajes',                         'AGRIND-903',      8],
                ['Industria Bebidas y Licores',                 'AGRIND-905',      8], // nueva
                ['Industria de Cacao y Café',                   'AGRIND-906',      8], // nueva
                ['Manejo de Materia Prima Animal y Vegetal',    'AGRIND-907',      8], // nueva
                ['Trabajo de Integración Curricular: Fase de Diseño',           'PAM-6301.1',  8], // antes: "TIC – Fase Diseño"
                ['Prácticas Preprofesionales I',                'PRAC-001',        8],

                // NIVEL 9
                ['Marco Legal Agroindustrial',                  'AGRIND-1003',     9],
                ['Legislación Agroindustrial',                  'AGRIND-1003B',    9], // ➕ nueva (distinta a Marco Legal)
                ['Investigación y Desarrollo de Productos Agroindustriales', 'AGRIND-1007', 9],
                ['Gestión de la Calidad',                       'AGRIND-804A',     9],
                ['Trabajo de Integración Curricular: Fase de Resultados e Informe', 'PAM-6301.2', 9], // nombre oficial
                ['Prácticas Preprofesionales II',               'PRAC-002',        9],
            ],

            // ─────────────────────────────────────────────────────────────
            // 2 · AGRONEGOCIOS
            // ─────────────────────────────────────────────────────────────
            2 => [
                // NIVEL 1
                ['Biología',                                    'AGRN-101',        1],
                ['Matemática',                                  'AGRN-102',        1],
                ['Química',                                     'AGRN-103',        1],
                ['Introducción a los Agronegocios',             'AGRN-104',        1],
                ['Cátedra Alfaro',                              'INST-CAAL',       1],
                ['Investigación Aplicada a los Agronegocios',   'AGRN-106',        1],

                // NIVEL 2
                ['Botánica',                                    'AGRN-201',        2],
                ['Matemática Financiera',                       'AGRN-202',        2],
                ['Estadística',                                 'AGRN-203',        2],
                ['Economía Global',                             'INST-ECGL',       2],
                ['Agricultura Ecológica y Alternativa',         'AGRN-205',        2],
                ['Ciencias del Suelo',                          'AGRN-206',        2],

                // NIVEL 3
                ['Contabilidad Agropecuaria',                   'AGRN-301',        3],
                ['Administración de la Producción',             'AGRN-302',        3],
                ['Legislación y Política Nacional',             'AGRN-303',        3],
                ['Sistemas de Producción Pecuario y Acuícola',  'AGRN-304',        3],
                ['Sistemas de Producción Agrícola',             'AGRN-305',        3],
                ['Optimización del Riego',                      'AGRN-306',        3],

                // NIVEL 4
                ['Contabilidad Financiera',                     'AGRN-401',        4],
                ['Teoría Macro y Microeconomía',                'AGRN-402',        4], // tilde en Microeconomía
                ['Innovación, Emprendimiento y Liderazgo',      'INST-INE',        4],
                ['Biotecnologías Agropecuarias',                'AGRN-404',        4], // tilde
                ['Manejo Postcosecha',                          'AGRN-405',        4],
                ['Sistemas de Información Geográfica',          'AGRN-406',        4],

                // NIVEL 5
                ['Economía Agrícola',                           'AGRN-501',        5],
                ['Modelo de Negocios',                          'AGRN-502',        5],
                ['Investigación de Mercados de Agro Biodiversidad', 'AGRN-503',   5],
                ['Gerencia Financiera',                         'AGRN-504',        5],
                ['Sistemas Agroindustriales',                   'AGRN-505',        5],
                ['Agricultura de Precisión',                    'AGRN-506',        5],

                // NIVEL 6
                ['Economía Popular y Solidaria',                'AGRN-601',        6],
                ['Formulación y Evaluación de Proyectos',       'AGRN-602',        6],
                ['Alimentos y Bioproductos',                    'AGRN-603',        6],
                ['Bioeconomía',                                 'AGRN-604',        6],
                ['Técnicas de Negociación',                     'AGRN-605',        6],
                ['Desarrollo Rural y Territorio',               'AGRN-606',        6],

                // NIVEL 7
                ['Mercado de Valores',                          'AGRN-701',        7],
                ['Bioemprendimientos Sostenibles',              'AGRN-702',        7],
                ['Planificación Estratégica',                   'AGRN-703',        7],
                ['Agrocomercio Exterior',                       'AGRN-704',        7],

                // NIVEL 8
                ['Gestión de Talento Humano',                   'AGRN-801',        8], // antes: "Gestión DEL Talento Humano"
                ['Marketing Agroalimentario',                   'AGRN-802',        8],
                ['Gerencia de Proyectos',                       'AGRN-803',        8],
                ['Sistemas Integrados de Gestión',              'AGRN-804',        8],
                ['Desarrollo Local',                            'AGRN-805',        8],
                ['Trabajo de Integración Curricular: Fase de Diseño',           'AGRN-TIC1',   8],
                ['Trabajo de Integración Curricular: Fase de Resultados e Informe', 'AGRN-TIC2', 8],
                ['Prácticas Preprofesionales I',                'AGRN-PRAC1',      8],
            ],

            // ─────────────────────────────────────────────────────────────
            // 3 · INGENIERÍA AGROPECUARIA
            // ─────────────────────────────────────────────────────────────
            3 => [
                // NIVEL 1
                ['Biología',                                    'AGR-101',         1],
                ['Química General',                             'AGR-102',         1],
                ['Matemática',                                  'AGR-103',         1],
                ['Metodología de la Investigación',             'AGR-104',         1],
                ['Introducción a las Ciencias Agropecuarias',   'AGR-105',         1],
                ['Cátedra Alfaro',                              'AGR-106',         1],

                // NIVEL 2
                ['Botánica General',                            'AGR-201',         2],
                ['Química Orgánica',                            'AGR-202',         2],
                ['Física',                                      'AGR-203',         2],
                ['Microbiología',                               'AGR-204',         2],
                ['Zoología General',                            'AGR-205',         2],
                ['Meteorología',                                'AGR-206',         2],

                // NIVEL 3
                ['Botánica Sistemática',                        'AGR-301',         3],
                ['Ciencias del Suelo',                          'AGR-302',         3],
                ['Topografía',                                  'AGR-303',         3],
                ['Sanidad Vegetal',                             'AGR-304',         3],
                ['Anatomía y Fisiología Animal',                'AGR-305',         3], // tilde en Anatomía
                ['Economía Global',                             'AGR-306',         3],

                // NIVEL 4
                ['Genética y Fitomejoramiento',                 'AGR-401',         4],
                ['Construcciones Agropecuarias',                'AGR-402',         4],
                ['Sistema de Información Geográfica',           'AGR-403',         4], // singular
                ['Nutrición Vegetal',                           'AGR-404',         4],
                ['Sanidad Animal',                              'AGR-405',         4],
                ['Agroecología en Zonas Áridas y Húmedas',      'AGR-406',         4], // "en" + tildes

                // NIVEL 5
                ['Innovación, Emprendimiento y Liderazgo',      'AGR-501',         5],
                ['Economía Agropecuaria',                       'AGR-502',         5],
                ['Sistemas de Riego y Drenaje',                 'AGR-503',         5],
                ['Agroforestería',                              'AGR-504',         5],
                ['Métodos Estadísticos y Diseño Experimental',  'AGR-505',         5], // tildes
                ['Mecanización Agropecuaria',                   'AGR-506',         5],

                // NIVEL 6
                ['Agricultura de Precisión',                    'AGR-601',         6],
                ['Fundamentos de la Agricultura Urbana y Periurbana', 'AGR-602',  6],
                ['Pastos y Forrajes',                           'AGR-603',         6],
                ['Sistema de Producción Agrícola: Ciclo Corto', 'AGR-604',        6], // singular
                ['Sistema de Producción Agrícola: Ciclo Perenne','AGR-605',        6], // singular
                ['Manejo Postcosecha',                          'AGR-606',         6],

                // NIVEL 7
                ['Nutrición Animal',                            'AGR-701',         7],
                ['Mejoramiento Animal',                         'AGR-702',         7],
                ['Sistema de Producción Pecuaria: Rumiantes',   'AGR-703',         7], // nombre oficial
                ['Sistema de Producción Pecuaria: Porcinos',    'AGR-704',         7], // nombre oficial
                ['Sistema de Producción Pecuaria: Especies Menores', 'AGR-705',   7], // nombre oficial
                ['Sistema de Producción Pecuaria: Avícola',     'AGR-706',         7],

                // NIVEL 8
                ['Gestión Administrativa y Financiera Agropecuaria', 'AGR-801',   8],
                ['Gestión Integral de la Calidad Agropecuaria', 'AGR-802',        8],
                ['Desarrollo Local',                            'AGR-803',         8],
                ['Biotecnología',                               'AGR-804',         8],
                ['Trabajo de Integración Curricular: Fase de Diseño',           'AGR-TIC1',    8],
                ['Trabajo de Integración Curricular: Fase de Resultados e Informe', 'AGR-TIC2', 8],
                ['Prácticas Preprofesionales I',                'AGR-PRAC1',       8],
                ['Prácticas Preprofesionales II',               'AGR-PRAC2',       8],

                // NIVEL 9
                ['Sociología Rural',                            'AGR-901',         9], // tilde
                ['Mercado y Comercialización Agropecuaria',     'AGR-902',         9],
                ['Marco Legal Agropecuario',                    'AGR-903',         9],
                ['Diseño y Evaluación de Proyectos',            'AGR-904',         9],
                ['Elaboración de Trabajo de Titulación',        'AGR-905',         9], // nueva
            ],
        ];

        DB::transaction(function () use ($carreras, $materiasPorCarrera) {
            foreach ($carreras as $carreraId => $prefijo) {

                if (DB::table('materias')->where('carrera_id', $carreraId)->exists()) {
                    $this->command?->warn("  Carrera {$carreraId} ya tiene materias, se omite.");
                    continue;
                }

                foreach ($materiasPorCarrera[$carreraId] as [$nombre, $codigo, $nivel]) {
                    DB::table('materias')->insert([
                        'carrera_id'  => $carreraId,
                        'nombre'      => $nombre,
                        'codigo'      => $codigo,
                        'creditos'    => 3,
                        'nivel'       => $nivel,
                        'activa'      => true,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }

                $total = count($materiasPorCarrera[$carreraId]);
                $this->command?->info("  Carrera {$carreraId} ({$prefijo}): {$total} materias insertadas.");
            }
        });

        $this->command?->info('MateriasSeeder completado.');
    }
}