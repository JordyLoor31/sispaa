<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DocentesSeeder extends Seeder
{
    public function run(): void
    {
 
        // Estructura: ['Nombre completo', 'email generado', 'carrera_id']
        // carrera_id: 1 = Agroindustrial | 2 = Agronegocios | 3 = Agropecuaria
        // Docentes que dictan en ambas carreras se registran con carrera_id null
        // y se les asigna vía asignaciones_docente
        // -------------------------------------------------------

        $docentes = [

            // ── AGROPECUARIA Y AGRONEGOCIOS ──────────────────────────────
            [
                'name'  => 'Byron Rolando Alcívar Arteaga',
                'email' => 'b.alcivar@universidad.edu.ec',
            ],
            [
                'name'  => 'Paola Rosalin Alcívar Vaca',
                'email' => 'p.alcivar@universidad.edu.ec',
            ],
            [
                'name'  => 'Mariana del Carmen Avellan Chancay',
                'email' => 'm.avellan@universidad.edu.ec',
            ],
            [
                'name'  => 'José Luis Brito Jurado',
                'email' => 'j.brito@universidad.edu.ec',
            ],
            [
                'name'  => 'Francisco Orley Cañarte García',
                'email' => 'f.canarte@universidad.edu.ec',
            ],
            [
                'name'  => 'Elizalde Exequiel Cárdenas Reyes',
                'email' => 'e.cardenas@universidad.edu.ec',
            ],
            [
                'name'  => 'Jeniffer Paulina Espinoza Zambrano',
                'email' => 'j.espinoza@universidad.edu.ec',
            ],
            [
                'name'  => 'María Gabriela Farias Delgado',
                'email' => 'm.farias@universidad.edu.ec',
            ],
            [
                'name'  => 'George Adalberto García Mera',
                'email' => 'g.garcia@universidad.edu.ec',
            ],
            [
                'name'  => 'Ángel Monserrate Guzmán Cedeño',
                'email' => 'a.guzman@universidad.edu.ec',
            ],
            [
                'name'  => 'Xavier Eloy Mata Loor',
                'email' => 'x.mata@universidad.edu.ec',
            ],
            [
                'name'  => 'Dídimo Alexander Mendoza Intriago',
                'email' => 'd.mendoza@universidad.edu.ec',
            ],
            [
                'name'  => 'Valter Francisco Mero Rosado',
                'email' => 'v.mero@universidad.edu.ec',
            ],
            [
                'name'  => 'Ramón Antonio Molina Basurto',
                'email' => 'r.molina@universidad.edu.ec',
            ],
            [
                'name'  => 'Ever Morales Avendaño',
                'email' => 'e.morales@universidad.edu.ec',
            ],
            [
                'name'  => 'María Belén Muñoz Menéndez',
                'email' => 'm.munoz@universidad.edu.ec',
            ],
            [
                'name'  => 'Diego Javier Nevárez Pérez',
                'email' => 'd.nevarez@universidad.edu.ec',
            ],
            [
                'name'  => 'Juan Carlos Palacios Peñafiel',
                'email' => 'j.palacios@universidad.edu.ec',
            ],
            [
                'name'  => 'Christian Simón Rivadeneira Barcia',
                'email' => 'c.rivadeneira@universidad.edu.ec',
            ],
            [
                'name'  => 'Liz Sabrina Trueba Macías',
                'email' => 'l.trueba@universidad.edu.ec',
            ],
            [
                'name'  => 'Miguel Bolívar Zambrano Reyes',
                'email' => 'm.zambrano@universidad.edu.ec',
            ],

            // ── AGROINDUSTRIA ────────────────────────────────────────────
            [
                'name'  => 'Carlos Eduardo Anchundia Betancourt',
                'email' => 'c.anchundia@universidad.edu.ec',
            ],
            [
                'name'  => 'José Isidro Andrade Vera',
                'email' => 'j.andrade@universidad.edu.ec',
            ],
            [
                'name'  => 'Geovanny Oswaldo Arauz Barcia',
                'email' => 'g.arauz@universidad.edu.ec',
            ],
            [
                'name'  => 'Julio Enrique Avila Roca',
                'email' => 'j.avila@universidad.edu.ec',
            ],
            [
                'name'  => 'Ítalo Pedro Bello Moreira',
                'email' => 'i.bello@universidad.edu.ec',
            ],
            [
                'name'  => 'José Antonio Cedeño Velasco',
                'email' => 'ja.cedeno@universidad.edu.ec',
            ],
            [
                'name'  => 'José Luis Coloma Hurel',
                'email' => 'j.coloma@universidad.edu.ec',
            ],
            [
                'name'  => 'Yessenia Maribel García Montes',
                'email' => 'y.garcia@universidad.edu.ec',
            ],
            [
                'name'  => 'Edison Grego Lavayen Delgado',
                'email' => 'e.lavayen@universidad.edu.ec',
            ],
            [
                'name'  => 'Aldo Eduardo Mendoza González',
                'email' => 'a.mendoza@universidad.edu.ec',
            ],
            [
                'name'  => 'Juan Robert Mero Santana',
                'email' => 'jr.mero@universidad.edu.ec',
            ],
            [
                'name'  => 'Víctor Oswaldo Otero Tuarez',
                'email' => 'v.otero@universidad.edu.ec',
            ],
            [
                'name'  => 'Ángel Del Jesús Prado Cedeño',
                'email' => 'a.prado@universidad.edu.ec',
            ],
            [
                'name'  => 'Stalin Gustavo Santacruz Terán',
                'email' => 's.santacruz@universidad.edu.ec',
            ],
        ];

        $creados   = 0;
        $existentes = 0;

        foreach ($docentes as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'               => $data['name'],
                    'password'           => Hash::make('Docente2026!'), // contraseña temporal
                    'email_verified_at'  => now(),
                    'is_active'          => true,
                    'carrera_id'         => null, 
                ]
            );

            if ($user->wasRecentlyCreated) {
                $creados++;
            } else {
                $existentes++;
            }
        }

        $this->command?->info("Docentes creados: {$creados} | Ya existían: {$existentes}");
        $this->command?->warn('Contraseña temporal de todos los docentes: Docente2026!');
        $this->command?->warn('Recuerda forzar cambio de contraseña en el primer login.');
    }
}