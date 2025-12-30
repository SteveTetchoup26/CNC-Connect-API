<?php

namespace Database\Seeders;

use App\Models\Centre;
use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionCentreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'code_region' => 'CE',
                'label_region' => 'Centre',
                'centres' => [
                    [
                        'libelle_centre' => 'Centre de dépôt Yaoundé',
                        'type_centre' => 'CENTRE_DE_DEPÔT',
                        'ville_centre' => 'Yaoundé',
                        'capacite' => 2000,
                    ],
                    [
                        'libelle_centre' => 'Centre d’examen Yaoundé',
                        'type_centre' => 'CENTRE_EXAMEN',
                        'ville_centre' => 'Yaoundé',
                        'capacite' => 1500,
                    ],
                ],
            ],
            [
                'code_region' => 'LT',
                'label_region' => 'Littoral',
                'centres' => [
                    [
                        'libelle_centre' => 'Centre de dépôt Douala',
                        'type_centre' => 'CENTRE_DE_DEPÔT',
                        'ville_centre' => 'Douala',
                        'capacite' => 2500,
                    ],
                    [
                        'libelle_centre' => 'Centre d’examen Douala',
                        'type_centre' => 'CENTRE_EXAMEN',
                        'ville_centre' => 'Douala',
                        'capacite' => 1800,
                    ],
                ],
            ],
        ];

        foreach ($data as $regionData) {

            /** 1️⃣ Région */
            $region = Region::firstOrCreate(
                ['code_region' => $regionData['code_region']],
                ['label_region' => $regionData['label_region']]
            );

            /** 2️⃣ Centres */
            foreach ($regionData['centres'] as $centreData) {
                Centre::firstOrCreate(
                    [
                        'libelle_centre' => $centreData['libelle_centre'],
                        'region_id' => $region->id,
                    ],
                    [
                        'type_centre' => $centreData['type_centre'],
                        'ville_centre' => $centreData['ville_centre'],
                        'capacite' => $centreData['capacite'],
                    ]
                );
            }
        }
    }
}
