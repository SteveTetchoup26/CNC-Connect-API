<?php

namespace Tests\Feature;

use App\Models\Ecole;
use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EcoleTest extends TestCase
{
    use RefreshDatabase;

    protected int $ecoleId;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake storage pour les images
        Storage::fake('public');

        // Admin authentifié
        $admin = Utilisateur::factory()->admin()->create();
        Sanctum::actingAs($admin);
    }

    /** @test */
    public function un_admin_peut_lister_les_ecoles()
    {
        Ecole::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/admin/ecoles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'libelle_ecole']
            ]);
    }

    /** @test */
    public function un_admin_peut_creer_une_ecole()
    {
        $payload = [
            'ecole' => [
                'libelle_ecole' => 'Université Test',
                'code_ecole' => 'UTEST',
                'localisation' => 'Yaoundé',
                'email_ecole' => 'contact@test.edu',
                'bp_ecole' => 'BP 123',
                'siteweb_ecole' => 'https://test.edu',
                'telephone_ecole' => '699000000',
                'logo' => UploadedFile::fake()->image('logo.png'),
            ],

            'departements' => [
                [
                    'libelle_departement' => 'Sciences',
                    'code_departement' => 'SCI',
                    'description_departement' => 'Département sciences',
                    'filieres' => [
                        [
                            'libelle_filiere' => 'Informatique',
                            'code_filiere' => 'INFO',
                            'description_filiere' => 'Filière info',
                            'niveaux' => [
                                [
                                    'libelle_niveau' => 'Licence 1',
                                    'code_niveau' => 'L1',
                                    'matieres' => [
                                        [
                                            'libelle_matiere' => 'Algorithmique',
                                            'code_matiere' => 'ALGO'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/admin/ecoles', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'École créée avec succès',
            ]);

        $this->assertDatabaseHas('ecoles', [
            'code_ecole' => 'UTEST'
        ]);
    }

    /** @test */
    public function un_admin_peut_afficher_une_ecole()
    {
        $ecole = Ecole::factory()->create();

        $response = $this->getJson("/api/v1/admin/ecoles/{$ecole->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'libelle_ecole'],
                'logo_url',
                'banniere_url',
                'embleme_url'
            ]);
    }

    /** @test */
    // public function un_admin_peut_mettre_a_jour_une_ecole()
    // {
    //     $ecole = Ecole::factory()->create();

    //     $payload = [
    //         'libelle_ecole' => 'Université Modifiée',
    //         'localisation' => 'Douala',
    //         'telephone_ecole' => '677111222',
    //         'departements' => [
    //             [
    //                 'libelle_departement' => 'Maths',
    //                 'code_departement' => 'MATH',
    //                 'description_departement' => 'Dept maths',
    //                 'filieres' => [
    //                     [
    //                         'libelle_filiere' => 'Statistiques',
    //                         'code_filiere' => 'STAT',
    //                         'description_filiere' => null,
    //                         'niveaux' => [
    //                             [
    //                                 'libelle_niveau' => 'Master 1',
    //                                 'code_niveau' => 'M1',
    //                                 'matieres' => [
    //                                     [
    //                                         'libelle_matiere' => 'Probabilités',
    //                                         'code_matiere' => 'PROBA'
    //                                     ]
    //                                 ]
    //                             ]
    //                         ]
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     ];

    //     $response = $this->putJson("/api/v1/admin/ecoles/{$ecole->id}", $payload);

    //     $response->assertStatus(200)
    //         ->assertJsonFragment([
    //             'message' => 'École mise à jour avec succès',
    //         ]);

    //     $this->assertDatabaseHas('ecoles', [
    //         'id' => $ecole->id,
    //         'libelle_ecole' => 'Université Modifiée'
    //     ]);
    // }

    /** @test */
    public function un_admin_peut_supprimer_une_ecole()
    {
        $ecole = Ecole::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/ecoles/{$ecole->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'École supprimée'
            ]);

        $this->assertDatabaseMissing('ecoles', [
            'id' => $ecole->id
        ]);
    }

}
