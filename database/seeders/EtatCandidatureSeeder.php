<?php

namespace Database\Seeders;

use App\Models\EtatCandidature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EtatCandidatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $etats = [
            [
                'libelle_etat' => 'EN_ATTENTE',
                'description_etat' => 'Candidature soumise et en attente de traitement'
            ],
            [
                'libelle_etat' => 'EN_COURS',
                'description_etat' => 'Candidature en cours de traitement'
            ],
            [
                'libelle_etat' => 'APPROUVEE',
                'description_etat' => 'Candidature validée et approuvée'
            ],
            [
                'libelle_etat' => 'REJETEE',
                'description_etat' => 'Candidature rejetée'
            ],
        ];

        foreach ($etats as $etat) {
            EtatCandidature::firstOrCreate(
                ['libelle_etat' => $etat['libelle_etat']],
                ['description_etat' => $etat['description_etat']]
            );
        }
    }
}
