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
             *  1️⃣ ÉCOLES
             *  =============================== */
            $ecoles = [
                [
                    'code_ecole' => 'ENSPY-POLYTECH',
                    'libelle_ecole' => 'Ecole Normale Superieure Polytechnique de Yaoundé',
                    'localisation' => 'Yaoundé',
                    'email_ecole' => 'contact@enspy.cm',
                    'bp_ecole' => 'BP 12345',
                    'siteweb_ecole' => 'https://www.enspy.cm',
                    'devise' => 'Excellence et Innovation',
                    'telephone_ecole' => '+237 222 123 456'
                ],
                [
                    'code_ecole' => 'ENSPD-POLYTECH',
                    'libelle_ecole' => 'Ecole Normale Superieure Polytechnique de Douala',
                    'localisation' => 'Douala',
                    'email_ecole' => 'info@polydla.cm',
                    'bp_ecole' => 'BP 23456',
                    'siteweb_ecole' => 'https://www.polydla.cm',
                    'devise' => 'Savoir et Développement',
                    'telephone_ecole' => '+237 233 234 567'
                ],
                [
                    'code_ecole' => 'ENSTP',
                    'libelle_ecole' => 'Ecole Normale Supérieure des Travaux Publiques',
                    'localisation' => 'Yaoundé',
                    'email_ecole' => 'contact@ufdst.cm',
                    'bp_ecole' => 'BP 34567',
                    'siteweb_ecole' => 'https://www.ufdst.cm',
                    'devise' => 'Science au service de la société',
                    'telephone_ecole' => '+237 244 345 678'
                ],
                [
                    'code_ecole' => 'ENS-MINES',
                    'libelle_ecole' => 'École Nationale Supérieure des Mines',
                    'localisation' => 'Garoua',
                    'email_ecole' => 'direction@ensmines.cm',
                    'bp_ecole' => 'BP 45678',
                    'siteweb_ecole' => 'https://www.ensmines.cm',
                    'devise' => 'Richesse et Progrès',
                    'telephone_ecole' => '+237 255 456 789'
                ],
                [
                    'code_ecole' => 'IAI-CAMEROUN',
                    'libelle_ecole' => 'Institut Africain d\'Informatique',
                    'localisation' => 'Yaoundé',
                    'email_ecole' => 'admissions@iai-cm.org',
                    'bp_ecole' => 'BP 56789',
                    'siteweb_ecole' => 'https://www.iai-cm.org',
                    'devise' => 'Numérique pour l\'Afrique',
                    'telephone_ecole' => '+237 222 567 890'
                ]
            ];

            /** ===============================
             *  2️⃣ STRUCTURE DÉPARTEMENTS ET FILIÈRES
             *  =============================== */
            $structures = [
                // Structure pour les écoles techniques
                [
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
                ],
                // Structure pour les écoles d'ingénieurs
                [
                    [
                        'libelle' => 'Génie Civil',
                        'code' => 'DEP-GC',
                        'description' => 'Département Génie Civil',
                        'filieres' => [
                            [
                                'libelle' => 'Bâtiment et Travaux Publics',
                                'code' => 'BTP',
                                'description' => 'Construction et infrastructures',
                                'niveaux' => ['LICENCE 1', 'LICENCE 2', 'LICENCE 3', 'MASTER 1', 'MASTER 2']
                            ],
                        ]
                    ],
                    [
                        'libelle' => 'Génie Mécanique',
                        'code' => 'DEP-GM',
                        'description' => 'Département Génie Mécanique',
                        'filieres' => [
                            [
                                'libelle' => 'Conception Mécanique',
                                'code' => 'CM',
                                'description' => 'Conception et fabrication',
                                'niveaux' => ['LICENCE 1', 'LICENCE 2', 'LICENCE 3', 'MASTER 1']
                            ],
                        ]
                    ]
                ],
                // Structure pour les universités scientifiques
                [
                    [
                        'libelle' => 'Biologie',
                        'code' => 'DEP-BIO',
                        'description' => 'Département de Biologie',
                        'filieres' => [
                            [
                                'libelle' => 'Biotechnologie',
                                'code' => 'BIOTECH',
                                'description' => 'Biotechnologies appliquées',
                                'niveaux' => ['LICENCE 1', 'LICENCE 2', 'LICENCE 3']
                            ],
                        ]
                    ],
                    [
                        'libelle' => 'Chimie',
                        'code' => 'DEP-CHIM',
                        'description' => 'Département de Chimie',
                        'filieres' => [
                            [
                                'libelle' => 'Chimie Industrielle',
                                'code' => 'CHIM-IND',
                                'description' => 'Chimie des procédés',
                                'niveaux' => ['LICENCE 1', 'LICENCE 2', 'LICENCE 3', 'MASTER 1']
                            ],
                        ]
                    ]
                ],
                // Structure pour les écoles des mines
                [
                    [
                        'libelle' => 'Géologie',
                        'code' => 'DEP-GEO',
                        'description' => 'Département de Géologie',
                        'filieres' => [
                            [
                                'libelle' => 'Géologie Minière',
                                'code' => 'GEOMIN',
                                'description' => 'Exploration minière',
                                'niveaux' => ['LICENCE 1', 'LICENCE 2', 'LICENCE 3', 'MASTER 1', 'MASTER 2']
                            ],
                        ]
                    ],
                    [
                        'libelle' => 'Génie Minier',
                        'code' => 'DEP-GMINE',
                        'description' => 'Département Génie Minier',
                        'filieres' => [
                            [
                                'libelle' => 'Exploitation Minière',
                                'code' => 'EXPLOIT',
                                'description' => 'Techniques d\'exploitation',
                                'niveaux' => ['LICENCE 1', 'LICENCE 2', 'LICENCE 3']
                            ],
                        ]
                    ]
                ],
                // Structure pour les instituts informatiques
                [
                    [
                        'libelle' => 'Développement Web',
                        'code' => 'DEP-DWEB',
                        'description' => 'Département Développement Web',
                        'filieres' => [
                            [
                                'libelle' => 'Développement Full Stack',
                                'code' => 'FULLSTACK',
                                'description' => 'Développement frontend et backend',
                                'niveaux' => ['LICENCE 1', 'LICENCE 2', 'LICENCE 3']
                            ],
                            [
                                'libelle' => 'Mobile Development',
                                'code' => 'MOBILE',
                                'description' => 'Développement d\'applications mobiles',
                                'niveaux' => ['LICENCE 2', 'LICENCE 3']
                            ],
                        ]
                    ],
                    [
                        'libelle' => 'Data Science',
                        'code' => 'DEP-DATA',
                        'description' => 'Département Data Science',
                        'filieres' => [
                            [
                                'libelle' => 'Intelligence Artificielle',
                                'code' => 'IA',
                                'description' => 'Machine Learning et IA',
                                'niveaux' => ['LICENCE 3', 'MASTER 1', 'MASTER 2']
                            ],
                        ]
                    ]
                ]
            ];

            /** ===============================
             *  3️⃣ INSERTION DES ÉCOLES ET LEURS STRUCTURES
             *  =============================== */
            foreach ($ecoles as $index => $ecoleData) {

                $ecole = Ecole::firstOrCreate(
                    ['code_ecole' => $ecoleData['code_ecole']],
                    [
                        'libelle_ecole' => $ecoleData['libelle_ecole'],
                        'localisation' => $ecoleData['localisation'],
                        'email_ecole' => $ecoleData['email_ecole'],
                        'bp_ecole' => $ecoleData['bp_ecole'],
                        'siteweb_ecole' => $ecoleData['siteweb_ecole'],
                        'devise' => $ecoleData['devise'],
                        'telephone_ecole' => $ecoleData['telephone_ecole'],
                    ]
                );

                // Assigner la structure correspondante à l'école
                if (isset($structures[$index])) {
                    foreach ($structures[$index] as $depData) {

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
                }
            }

            // Message de confirmation
            $this->command->info('✅ 5 écoles créées avec succès avec leurs départements et filières !');
        });
    }
}
