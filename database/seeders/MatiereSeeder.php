<?php

namespace Database\Seeders;

use App\Models\Matiere;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MatiereSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $matieres = [
            ['libelle_matiere' => 'Mathématiques', 'code_matiere' => 'MATH'],
            ['libelle_matiere' => 'Physique', 'code_matiere' => 'PHY'],
            ['libelle_matiere' => 'Chimie', 'code_matiere' => 'CHIM'],
            ['libelle_matiere' => 'Informatique', 'code_matiere' => 'INFO'],
            ['libelle_matiere' => 'Algorithmique', 'code_matiere' => 'ALGO'],
            ['libelle_matiere' => 'Anglais', 'code_matiere' => 'ANG'],
        ];

        foreach ($matieres as $matiere) {
            Matiere::firstOrCreate(
                ['code_matiere' => $matiere['code_matiere']],
                $matiere
            );
        }
    }
}
