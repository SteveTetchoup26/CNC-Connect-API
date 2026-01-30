<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class CentreTest extends TestCase
{
    use RefreshDatabase;

    protected ?int $regionId = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Auth admin
        $admin = Utilisateur::factory()->admin()->create();
        Sanctum::actingAs($admin);

        // Récupérer une région existante
        $this->regionId = DB::table('regions')->inRandomOrder()->value('id');

        // Si aucune région n’existe (cas test)
        if ($this->regionId === null) {
            $this->regionId = DB::table('regions')->insertGetId([
                'code_region' => 'CE',
                'label_region' => 'Centre',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Lister les centres
     */
    public function test_un_admin_peut_lister_les_centres()
    {
        DB::table('centres')->insert([
            [
                'libelle_centre' => 'Centre Dépôt',
                'type_centre' => 'CENTRE_DE_DEPÔT',
                'ville_centre' => 'Yaoundé',
                'capacite' => 50,
                'region_id' => $this->regionId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'libelle_centre' => 'Centre Examen',
                'type_centre' => 'CENTRE_EXAMEN',
                'ville_centre' => 'Douala',
                'capacite' => 100,
                'region_id' => $this->regionId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->getJson('/api/v1/centres');

        $response
            ->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['libelle_centre' => 'Centre Dépôt']);
    }

    /**
     * Créer un centre
     */
    public function test_un_admin_peut_creer_un_centre()
    {
        $payload = [
            'libelle_centre' => 'Centre Principal',
            'type_centre' => 'CENTRE_EXAMEN',
            'ville_centre' => 'Bafoussam',
            'capacite' => 120,
            'region_id' => $this->regionId,
        ];

        $response = $this->postJson('/api/v1/admin/centres', $payload);

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Centre créé avec succès')
            ->assertJsonPath('centre.libelle_centre', 'Centre Principal');

        $this->assertDatabaseHas('centres', [
            'libelle_centre' => 'Centre Principal',
            'type_centre' => 'CENTRE_EXAMEN',
            'ville_centre' => 'Bafoussam',
            'region_id' => $this->regionId,
        ]);
    }

    /**
     * Afficher un centre
     */
    public function test_un_admin_peut_afficher_un_centre()
    {
        $centreId = DB::table('centres')->insertGetId([
            'libelle_centre' => 'Centre Show',
            'type_centre' => 'CENTRE_DE_DEPÔT',
            'ville_centre' => 'Garoua',
            'capacite' => 40,
            'region_id' => $this->regionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson("/api/v1/admin/centres/{$centreId}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('libelle_centre', 'Centre Show')
            ->assertJsonPath('region.id', $this->regionId);
    }

    /**
     * Mettre à jour un centre
     */
    public function test_un_admin_peut_modifier_un_centre()
    {
        $centreId = DB::table('centres')->insertGetId([
            'libelle_centre' => 'Ancien centre',
            'type_centre' => 'CENTRE_DE_DEPÔT',
            'ville_centre' => 'Maroua',
            'capacite' => 30,
            'region_id' => $this->regionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'libelle_centre' => 'Centre Modifié',
            'type_centre' => 'CENTRE_EXAMEN',
            'ville_centre' => 'Ngaoundéré',
            'capacite' => 200,
            'region_id' => $this->regionId,
        ];

        $response = $this->putJson("/api/v1/admin/centres/{$centreId}", $payload);

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'Centre mis à jour avec succès');

        $this->assertDatabaseHas('centres', [
            'id' => $centreId,
            'libelle_centre' => 'Centre Modifié',
            'type_centre' => 'CENTRE_EXAMEN',
            'ville_centre' => 'Ngaoundéré',
            'capacite' => 200,
        ]);
    }

    /**
     * Supprimer un centre
     */
    public function test_un_admin_peut_supprimer_un_centre()
    {
        $centreId = DB::table('centres')->insertGetId([
            'libelle_centre' => 'Centre à supprimer',
            'type_centre' => 'CENTRE_DE_DEPÔT',
            'ville_centre' => 'Ebolowa',
            'capacite' => 25,
            'region_id' => $this->regionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->deleteJson("/api/v1/admin/centres/{$centreId}");

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Centre supprimé avec succès']);

        $this->assertDatabaseMissing('centres', [
            'id' => $centreId,
        ]);
    }
}
