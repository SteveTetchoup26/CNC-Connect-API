<?php

namespace Database\Seeders;

use App\Models\EtatSession;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EtatSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $etats = [
            [
                'libelle_etat' => 'OUVERTE',
                'description_etat' => 'Session ouverte, inscriptions et concours autorisés'
            ],
            [
                'libelle_etat' => 'FERMEE',
                'description_etat' => 'Session fermée, aucune opération autorisée'
            ],
        ];

        foreach ($etats as $etat) {
            EtatSession::firstOrCreate(
                ['libelle_etat' => $etat['libelle_etat']],
                ['description_etat' => $etat['description_etat']]
            );
        }
    }
}
