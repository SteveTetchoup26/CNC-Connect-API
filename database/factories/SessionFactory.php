<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\EtatSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFactory extends Factory
{
    protected $model = Session::class;

    public function definition()
    {
        // On récupère ou crée l'état par défaut "OUVERTE"
        $etatOuverte = EtatSession::firstOrCreate(
            ['libelle_etat' => 'OUVERTE'],
            ['description_etat' => 'Session ouverte']
        );

        return [
            'libelle_session' => $this->faker->sentence(2),
            'description_session' => $this->faker->paragraph(1),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Attacher automatiquement l'état par défaut à l'historique
     */
    public function configure()
    {
        return $this->afterCreating(function (Session $session) {
            $etatOuverte = EtatSession::firstOrCreate(
                ['libelle_etat' => 'OUVERTE'],
                ['description_etat' => 'Session ouverte']
            );

            $session->historiques()->create([
                'etat_id' => $etatOuverte->id,
                'date_etat' => now(),
            ]);
        });
    }
}
