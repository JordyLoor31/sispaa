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
                'email' => 'b.alcivar@uleam.edu.ec',
            ],
            [
                'name'  => 'Paola Rosalin Alcívar Vaca',
                'email' => 'p.alcivar@uleam.edu.ec',
            ],
            [
                'name'  => 'Mariana del Carmen Avellan Chancay',
                'email' => 'm.avellan@uleam.edu.ec',
            ],
            [
                'name'  => 'José Luis Brito Jurado',
                'email' => 'j.brito@uleam.edu.ec',
            ],
            [
                'name'  => 'Francisco Orley Cañarte García',
                'email' => 'f.canarte@uleam.edu.ec',
            ],
            [
                'name'  => 'Elizalde Exequiel Cárdenas Reyes',
                'email' => 'e.cardenas@uleam.edu.ec',
            ],
            [
                'name'  => 'Jeniffer Paulina Espinoza Zambrano',
                'email' => 'j.espinoza@uleam.edu.ec',
            ],
            [
                'name'  => 'María Gabriela Farias Delgado',
                'email' => 'm.farias@uleam.edu.ec',
            ],
            [
                'name'  => 'George Adalberto García Mera',
                'email' => 'g.garcia@uleam.edu.ec',
            ],
            [
                'name'  => 'Ángel Monserrate Guzmán Cedeño',
                'email' => 'a.guzman@uleam.edu.ec',
            ],
            [
                'name'  => 'Xavier Eloy Mata Loor',
                'email' => 'x.mata@uleam.edu.ec',
            ],
            [
                'name'  => 'Dídimo Alexander Mendoza Intriago',
                'email' => 'd.mendoza@uleam.edu.ec',
            ],
            [
                'name'  => 'Valter Francisco Mero Rosado',
                'email' => 'v.mero@uleam.edu.ec',
            ],
            [
                'name'  => 'Ramón Antonio Molina Basurto',
                'email' => 'r.molina@uleam.edu.ec',
            ],
            [
                'name'  => 'Ever Morales Avendaño',
                'email' => 'e.morales@uleam.edu.ec',
            ],
            [
                'name'  => 'María Belén Muñoz Menéndez',
                'email' => 'm.munoz@uleam.edu.ec',
            ],
            [
                'name'  => 'Diego Javier Nevárez Pérez',
                'email' => 'd.nevarez@uleam.edu.ec',
            ],
            [
                'name'  => 'Juan Carlos Palacios Peñafiel',
                'email' => 'j.palacios@uleam.edu.ec',
            ],
            [
                'name'  => 'Christian Simón Rivadeneira Barcia',
                'email' => 'c.rivadeneira@uleam.edu.ec',
            ],
            [
                'name'  => 'Liz Sabrina Trueba Macías',
                'email' => 'l.trueba@uleam.edu.ec',
            ],
            [
                'name'  => 'Miguel Bolívar Zambrano Reyes',
                'email' => 'm.zambrano@uleam.edu.ec',
            ],

            // ── AGROINDUSTRIA ────────────────────────────────────────────
            [
                'name'  => 'Carlos Eduardo Anchundia Betancourt',
                'email' => 'c.anchundia@uleam.edu.ec',
            ],
            [
                'name'  => 'José Isidro Andrade Vera',
                'email' => 'j.andrade@uleam.edu.ec',
            ],
            [
                'name'  => 'Geovanny Oswaldo Arauz Barcia',
                'email' => 'g.arauz@uleam.edu.ec',
            ],
            [
                'name'  => 'Julio Enrique Avila Roca',
                'email' => 'j.avila@uleam.edu.ec',
            ],
            [
                'name'  => 'Ítalo Pedro Bello Moreira',
                'email' => 'i.bello@uleam.edu.ec',
            ],
            [
                'name'  => 'José Antonio Cedeño Velasco',
                'email' => 'ja.cedeno@uleam.edu.ec',
            ],
            [
                'name'  => 'José Luis Coloma Hurel',
                'email' => 'j.coloma@uleam.edu.ec',
            ],
            [
                'name'  => 'Yessenia Maribel García Montes',
                'email' => 'y.garcia@uleam.edu.ec',
            ],
            [
                'name'  => 'Edison Grego Lavayen Delgado',
                'email' => 'e.lavayen@uleam.edu.ec',
            ],
            [
                'name'  => 'Aldo Eduardo Mendoza González',
                'email' => 'a.mendoza@uleam.edu.ec',
            ],
            [
                'name'  => 'Juan Robert Mero Santana',
                'email' => 'jr.mero@uleam.edu.ec',
            ],
            [
                'name'  => 'Víctor Oswaldo Otero Tuarez',
                'email' => 'v.otero@uleam.edu.ec',
            ],
            [
                'name'  => 'Ángel Del Jesús Prado Cedeño',
                'email' => 'a.prado@uleam.edu.ec',
            ],
            [
                'name'  => 'Stalin Gustavo Santacruz Terán',
                'email' => 's.santacruz@uleam.edu.ec',
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

            $user->assignRole('docente');

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