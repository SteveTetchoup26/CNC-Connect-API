<?php

namespace Database\Seeders;

use App\Models\Matiere;
use App\Models\Niveau;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NiveauSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $niveaux = [
            'LICENCE 1' => ['MATH', 'PHY', 'INFO', 'ANG'],
            'LICENCE 2' => ['MATH', 'PHY', 'ALGO', 'INFO'],
            'LICENCE 3' => ['ALGO', 'INFO'],
            'MASTER 1'  => ['ALGO', 'INFO'],
            'MASTER 2'  => ['INFO'],
        ];

        foreach ($niveaux as $libelle => $codesMatieres) {

            $niveau = Niveau::firstOrCreate(
                ['libelle_niveau' => $libelle],
                ['code_niveau' => str_replace(' ', '_', strtoupper($libelle))]
            );

            // Récupération des matières par code
            $matiereIds = Matiere::whereIn('code_matiere', $codesMatieres)
                                  ->pluck('id');

            // Association pivot niveau_matieres
            $niveau->matieres()->syncWithoutDetaching($matiereIds);
        }
    }
}
