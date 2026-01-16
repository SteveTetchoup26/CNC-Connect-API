<?php

namespace Database\Seeders;

use App\Models\Concours;
use App\Models\SpecificationConcours;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecificationConcoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer tous les concours
        $concours = Concours::all();

        if ($concours->isEmpty()) {
            $this->command->error('Aucun concours trouvé dans la base de données.');
            return;
        }

        $this->command->info("Création des spécifications pour {$concours->count()} concours...");

        // Configuration unique pour tous les concours (comme demandé)
        $specificationsCommunes = [
            'desc_infos_concours' => 'Documents obligatoires pour l\'inscription au concours d\'excellence.',
            'carte_nationale_identite' => true,
            'diplomes' => true,
            'certificat_nationalite' => false,
            'releve_notes' => true,
            'acte_naissance' => true,
            'photo' => true
        ];

        $count = 0;

        foreach ($concours as $concour) {
            // Vérifier si une spécification existe déjà pour ce concours
            $existe = SpecificationConcours::where('concours_id', $concour->id)->exists();

            if (!$existe) {
                SpecificationConcours::create([
                    'concours_id' => $concour->id,
                    'desc_infos_concours' => $specificationsCommunes['desc_infos_concours'],
                    'carte_nationale_identite' => $specificationsCommunes['carte_nationale_identite'],
                    'diplomes' => $specificationsCommunes['diplomes'],
                    'certificat_nationalite' => $specificationsCommunes['certificat_nationalite'],
                    'releve_notes' => $specificationsCommunes['releve_notes'],
                    'acte_naissance' => $specificationsCommunes['acte_naissance'],
                    'photo' => $specificationsCommunes['photo']
                ]);

                $count++;
            }
        }

        $this->command->info("{$count} spécifications de concours créées avec succès !");
    }
}
