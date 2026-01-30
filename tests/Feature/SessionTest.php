<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = Utilisateur::factory()->admin()->create();
        Sanctum::actingAs($admin);
    }

    public function test_un_admin_peut_lister_les_sessions()
    {
        DB::table('sessions')->insert([
            [
                'libelle_session' => 'Session A',
                'description_session' => 'Description A',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'libelle_session' => 'Session B',
                'description_session' => 'Description B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->getJson('/api/v1/admin/sessions');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_un_admin_peut_creer_une_session()
    {
        $payload = [
            'libelle_session' => 'Session 2026',
            'description_session' => 'Session de test',
        ];

        $response = $this->postJson('/api/v1/admin/sessions', $payload);

        $response
            ->assertStatus(201)
            ->assertJsonPath('session.libelle_session', 'Session 2026');

        $this->assertDatabaseHas('sessions', [
            'libelle_session' => 'Session 2026',
        ]);

        // état OUVERTE créé
        $this->assertDatabaseHas('etat_sessions', [
            'libelle_etat' => 'OUVERTE',
        ]);

        // historique créé
        $this->assertDatabaseCount('historique_sessions', 1);
    }

    public function test_un_admin_peut_afficher_une_session()
    {
        $sessionId = DB::table('sessions')->insertGetId([
            'libelle_session' => 'Session Show',
            'description_session' => 'Description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/admin/sessions/{$sessionId}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('libelle_session', 'Session Show');
    }

    public function test_un_admin_peut_modifier_une_session()
    {
        $sessionId = DB::table('sessions')->insertGetId([
            'libelle_session' => 'Ancienne session',
            'description_session' => 'Ancienne description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $etatFermeeId = DB::table('etat_sessions')->insertGetId([
            'libelle_etat' => 'FERMEE',
            'description_etat' => 'Session fermée',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->putJson("/api/v1/admin/sessions/{$sessionId}", [
            'libelle_session' => 'Session modifiée',
            'etat_id' => $etatFermeeId,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('sessions', [
            'id' => $sessionId,
            'libelle_session' => 'Session modifiée',
        ]);

        $this->assertDatabaseHas('historique_sessions', [
            'session_id' => $sessionId,
            'etat_id' => $etatFermeeId,
        ]);
    }

    public function test_un_admin_peut_supprimer_une_session()
    {
        $sessionId = DB::table('sessions')->insertGetId([
            'libelle_session' => 'Session à supprimer',
            'description_session' => 'À supprimer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/api/v1/admin/sessions/{$sessionId}");

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Session supprimée avec succès']);

        $this->assertDatabaseMissing('sessions', [
            'id' => $sessionId,
        ]);
    }
}
