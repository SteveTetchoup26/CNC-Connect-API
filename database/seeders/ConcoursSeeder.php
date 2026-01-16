<?php

namespace Database\Seeders;

use App\Models\Concours;
use App\Models\Ecole;
use App\Models\Filiere;
use App\Models\Session;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConcoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            // Récupérer la dernière session
            $session = Session::latest('id')->first();

            if (!$session) {
                $this->command->error('❌ Aucune session trouvée. Veuillez d\'abord créer une session.');
                return;
            }

            $this->command->info("📅 Utilisation de la session : {$session->libelle_session}");

            // Récupérer toutes les écoles
            $ecoles = Ecole::all();

            if ($ecoles->isEmpty()) {
                $this->command->error('❌ Aucune école trouvée. Veuillez d\'abord exécuter EcoleSeeder.');
                return;
            }

            // Données de base pour les concours
            $concoursData = [
                [
                    'libelle' => 'Concours d\'entrée en première année du cycle ingénieur',
                    'frais_concours' => [
                        'montant_frais' => 50000,
                        'nom_banque' => 'ECOBANK',
                        'numero_compte' => '1234567890'
                    ]
                ],
                [
                    'libelle' => 'Concours d\'entrée en première année du cycle ingénieur',
                    'frais_concours' => [
                        'montant_frais' => 50000,
                        'nom_banque' => 'ECOBANK',
                        'numero_compte' => '1234567890'
                    ]
                ],
                [
                    'libelle' => 'Concours d\'entrée Sciences Biologiques',
                    'frais_concours' => [
                        'montant_frais' => 50000,
                        'nom_banque' => 'ECOBANK',
                        'numero_compte' => '1234567890'
                    ]
                ],
                [
                    'libelle' => 'Concours Mines et Géologie',
                    'frais_concours' => [
                        'montant_frais' => 50000,
                        'nom_banque' => 'ECOBANK',
                        'numero_compte' => '1234567890'
                    ]
                ],
                [
                    'libelle' => 'Concours d\'entrée Développement Digital',
                    'frais_concours' => [
                        'montant_frais' => 50000,
                        'nom_banque' => 'ECOBANK',
                        'numero_compte' => '1234567890'
                    ]
                ]
            ];

            $currentYear = date('Y');
            $nextYear = $currentYear + 1;

            foreach ($ecoles as $index => $ecole) {

                // Vérifier si un concours existe déjà pour cette école et cette session
                // $existingConcours = Concours::where('ecole_id', $ecole->id)
                //     ->where('session_id', $session->id)
                //     ->exists();

                // if ($existingConcours) {
                //     $this->command->info("⏭️ Concours déjà existant pour {$ecole->libelle_ecole}, session ignorée.");
                //     continue;
                // }

                // Récupérer les filières de l'école
                $filieres = Filiere::whereHas('departement', function($query) use ($ecole) {
                    $query->where('ecole_id', $ecole->id);
                })->get();

                if ($filieres->isEmpty()) {
                    $this->command->warn("⚠️ Aucune filière trouvée pour {$ecole->libelle_ecole}, concours ignoré.");
                    continue;
                }

                // Préparer les données des filières avec nombre de places
                $filieresConcours = [];
                foreach ($filieres as $filiere) {
                    // Nombre de places aléatoire entre 20 et 100
                    $nbrePlaces = rand(20, 100);
                    $filieresConcours[] = [
                        'id' => $filiere->id,
                        'nbre_places' => $nbrePlaces
                    ];
                }

                // Dates pour le concours
                $dateLimite = date('Y-m-d', strtotime("+3 months"));
                $dateExamen = date('Y-m-d', strtotime("+4 months"));

                // Utiliser les données spécifiques ou des valeurs par défaut
                $concoursInfo = $concoursData[$index] ?? [
                    'libelle' => "Concours d'entrée {$ecole->libelle_ecole} {$nextYear}",
                    'frais_concours' => [
                        'montant_frais' => 25000,
                        'nom_banque' => 'ECOBANK',
                        'numero_compte' => '00123456789'
                    ]
                ];

                // Créer le concours
                $concours = Concours::create([
                    'libelle_concours' => $concoursInfo['libelle'],
                    'date_limite' => $dateLimite,
                    'date_examen' => $dateExamen,
                    'ecole_id' => $ecole->id,
                    'frais_concours' => json_encode($concoursInfo['frais_concours']),
                    'is_active' => true,
                    'description' => "Concours d'entrée pour l'année académique {$nextYear}-" . ($nextYear + 1) . " à {$ecole->libelle_ecole}"
                ]);

                // Attacher les filières avec le nombre de places
                foreach ($filieresConcours as $filiereData) {
                    $concours->filieres()->attach($filiereData['id'], [
                        'nbre_places' => $filiereData['nbre_places'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $this->command->info("✅ Concours créé pour {$ecole->libelle_ecole} avec {$filieres->count()} filières.");
            }

            $this->command->info('🎉 Tous les concours ont été créés avec succès !');
        });
    }
}
