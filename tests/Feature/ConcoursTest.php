<?php

namespace Tests\Feature;

use App\Models\Concours;
use App\Models\Filiere;
use App\Models\Session;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConcoursTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un admin et se connecter
        $admin = Utilisateur::factory()->admin()->create();
        Sanctum::actingAs($admin, ['*']);
    }

    /** @test */
    public function un_admin_peut_lister_les_concours()
    {
        Concours::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/concours');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function un_admin_peut_creer_un_concours()
    {
        $session = Session::factory()->create();
        $filiere = Filiere::factory()->create();

        $payload = [
            'libelle_concours' => 'Concours Test',
            'date_limite' => now()->addDays(10)->toDateString(),
            'date_examen' => now()->addDays(20)->toDateString(),
            'session_id' => $session->id,
            'filieres' => [
                ['id' => $filiere->id, 'nbre_places' => 30]
            ],
            'frais_concours' => [
                'montant_frais' => 5000,
                'nom_banque' => 'Banque Test',
                'numero_compte' => '1234567890'
            ]
        ];

        $response = $this->postJson('/api/v1/admin/concours', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Concours créé avec succès',
                     'concours' => ['libelle_concours' => 'Concours Test']
                 ]);

        $this->assertDatabaseHas('concours', ['libelle_concours' => 'Concours Test']);
    }

    /** @test */
    // public function un_admin_peut_mettre_a_jour_un_concours()
    // {
    //     $concours = Concours::factory()->create([
    //         'libelle_concours' => 'Ancien Concours',
    //     ]);

    //     $payload = [
    //         'libelle_concours' => 'Nouveau Concours'
    //     ];

    //     $response = $this->putJson("/api/v1/admin/concours/{$concours->id}", $payload);

    //     $response->assertStatus(200)
    //              ->assertJson([
    //                  'message' => 'Concours mis à jour avec succès',
    //                  'concours' => ['libelle_concours' => 'Nouveau Concours']
    //              ]);

    //     $this->assertDatabaseHas('concours', ['libelle_concours' => 'Nouveau Concours']);
    // }

    /** @test */
    public function un_admin_peut_supprimer_un_concours()
    {
        $concours = Concours::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/concours/{$concours->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Concours et ses frais associés supprimés avec succès']);

        $this->assertDatabaseMissing('concours', ['id' => $concours->id]);
    }

    /** @test */
    public function un_admin_peut_afficher_un_concours()
    {
        $concours = Concours::factory()->create();

        $response = $this->getJson("/api/v1/concours/{$concours->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'concours' => ['id' => $concours->id]
                 ]);
    }
}
