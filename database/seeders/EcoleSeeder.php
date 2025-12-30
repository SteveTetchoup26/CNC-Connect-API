<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\Ecole;
use App\Models\Filiere;
use App\Models\Niveau;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EcoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            /** ===============================
             *  1️⃣ ÉCOLE
             *  =============================== */
            $ecole = Ecole::firstOrCreate(
                ['code_ecole' => 'ENS-TECH'],
                [
                    'libelle_ecole' => 'École Nationale des Sciences et Technologies',
                    'localisation' => 'Yaoundé',
                    'email_ecole' => 'contact@enstech.cm',
                ]
            );

            /** ===============================
             *  2️⃣ STRUCTURE DÉPARTEMENTS
             *  =============================== */
            $structure = [
                [
                    'libelle' => 'Informatique',
                    'code' => 'DEP-INF',
                    'description' => 'Département Informatique',
                    'filieres' => [
                        [
                            'libelle' => 'Génie Logiciel',
                            'code' => 'GL',
                            'description' => 'Développement logiciel',
                            'niveaux' => ['LICENCE 1', 'LICENCE 2', 'LICENCE 3', 'MASTER 1', 'MASTER 2']
                        ],
                        [
                            'libelle' => 'Systèmes et Réseaux',
                            'code' => 'SR',
                            'description' => 'Administration systèmes et réseaux',
                            'niveaux' => ['LICENCE 1', 'LICENCE 2', 'LICENCE 3']
                        ],
                    ]
                ],
                [
                    'libelle' => 'Sciences Fondamentales',
                    'code' => 'DEP-SF',
                    'description' => 'Mathématiques et Physique',
                    'filieres' => [
                        [
                            'libelle' => 'Mathématiques Appliquées',
                            'code' => 'MATH-APP',
                            'description' => 'Mathématiques appliquées',
                            'niveaux' => ['LICENCE 1', 'LICENCE 2', 'LICENCE 3']
                        ],
                    ]
                ]
            ];

            /** ===============================
             *  3️⃣ INSERTION
             *  =============================== */
            foreach ($structure as $depData) {

                $departement = Departement::firstOrCreate(
                    ['code_departement' => $depData['code']],
                    [
                        'libelle_departement' => $depData['libelle'],
                        'description_departement' => $depData['description'],
                        'ecole_id' => $ecole->id,
                    ]
                );

                foreach ($depData['filieres'] as $filData) {

                    $filiere = Filiere::firstOrCreate(
                        ['code_filiere' => $filData['code']],
                        [
                            'libelle_filiere' => $filData['libelle'],
                            'description_filiere' => $filData['description'],
                            'departement_id' => $departement->id,
                        ]
                    );

                    // Association filière ↔ niveaux
                    $niveauIds = Niveau::whereIn('libelle_niveau', $filData['niveaux'])
                                       ->pluck('id');

                    $filiere->niveaux()->syncWithoutDetaching($niveauIds);
                }
            }
        });
    }
}
