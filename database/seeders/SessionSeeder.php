<?php

namespace Database\Seeders;

use App\Models\EtatSession;
use App\Models\HistoriqueSession;
use App\Models\Session;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer l’état OUVERTE
        $etatOuverte = EtatSession::where('libelle_etat', 'OUVERTE')->first();

        if (!$etatOuverte) {
            throw new \Exception("L'état OUVERTE doit exister avant de seed les sessions");
        }

        $sessions = [
            [
                'libelle_session' => 'Session 2024 - 2025',
                'description_session' => 'Session académique 2024-2025',
            ],
            [
                'libelle_session' => 'Session 2025 - 2026',
                'description_session' => 'Session académique 2025-2026',
            ],
        ];

        foreach ($sessions as $data) {

            /** 1️⃣ Création de la session */
            $session = Session::firstOrCreate(
                ['libelle_session' => $data['libelle_session']],
                ['description_session' => $data['description_session']]
            );

            /** 2️⃣ Historique initial = OUVERTE */
            HistoriqueSession::firstOrCreate(
                [
                    'session_id' => $session->id,
                    'etat_id' => $etatOuverte->id,
                ],
                [
                    'date_etat' => Carbon::now(),
                ]
            );
        }
    }
}
