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
                'code_region' => 'AD',
                'label_region' => 'Adamaoua',
            ],
            [
                'code_region' => 'CE',
                'label_region' => 'Centre',
            ],
            [
                'code_region' => 'ES',
                'label_region' => 'Est',
            ],
            [
                'code_region' => 'EN',
                'label_region' => 'Extrême-Nord',
            ],
            [
                'code_region' => 'LT',
                'label_region' => 'Littoral',
            ],
            [
                'code_region' => 'NO',
                'label_region' => 'Nord',
            ],
            [
                'code_region' => 'NW',
                'label_region' => 'Nord-Ouest',
            ],
            [
                'code_region' => 'OU',
                'label_region' => 'Ouest',
            ],
            [
                'code_region' => 'SU',
                'label_region' => 'Sud',
            ],
            [
                'code_region' => 'SW',
                'label_region' => 'Sud-Ouest',
            ],
        ];

        foreach ($data as $regionData) {

            $region = Region::firstOrCreate(
                ['code_region' => $regionData['code_region']],
                ['label_region' => $regionData['label_region']]
            );

            // /** Centres */
            // foreach ($regionData['centres'] as $centreData) {
            //     Centre::firstOrCreate(
            //         [
            //             'libelle_centre' => $centreData['libelle_centre'],
            //             'region_id' => $region->id,
            //         ],
            //         [
            //             'type_centre' => $centreData['type_centre'],
            //             'ville_centre' => $centreData['ville_centre'],
            //             'capacite' => $centreData['capacite'],
            //         ]
            //     );
            // }
        }
    }
}
