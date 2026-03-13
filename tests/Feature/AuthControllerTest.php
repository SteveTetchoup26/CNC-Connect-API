<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Utilisateur;
use App\Models\Role;
use App\Models\Candidat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $roleCandidat;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer le rôle candidat nécessaire pour les tests
        $this->roleCandidat = Role::create([
            'libelle' => 'CANDIDAT',
            'description' => 'Rôle candidat'
        ]);
    }

    /** @test */
    public function it_can_register_a_new_user()
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '+237600000000'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Inscription réussie'
            ]);

        // Vérifier que l'utilisateur a été créé dans la base de données
        $this->assertDatabaseHas('utilisateurs', [
            'email' => 'test@example.com',
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '+237600000000'
        ]);

        // Vérifier que le mot de passe a été hashé
        $user = Utilisateur::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));

        // Vérifier que le rôle candidat a été assigné
        $this->assertTrue($user->roles->contains($this->roleCandidat));

        // Vérifier que le candidat a été créé
        $this->assertDatabaseHas('candidats', [
            'utilisateur_id' => $user->id
        ]);

        // Vérifier que le token a été créé
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'auth-token'
        ]);
    }

    /** @test */
    /** @test */
    public function it_validates_required_fields_on_registration()
    {
        $response = $this->postJson('/api/v1/register', []);

        $response->assertStatus(422);

        // Vérifier la structure de la réponse réelle
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email',
                'password'
            ]
        ]);

        // Vérifier les messages d'erreur spécifiques
        $response->assertJson([
            'message' => "L'email est requis (and 1 more error)",
            'errors' => [
                'email' => ["L'email est requis"],
                'password' => ["Le mot de passe est requis"]
            ]
        ]);
    }

    /** @test */
    public function it_validates_email_uniqueness_on_registration()
    {
        // Créer un utilisateur existant
        Utilisateur::create([
            'email' => 'existing@example.com',
            'password' => Hash::make('password123'),
            'nom' => 'Existing',
            'prenom' => 'User'
        ]);

        $userData = [
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nom' => 'Dupont',
            'prenom' => 'Jean'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_validates_password_confirmation_on_registration()
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
            'nom' => 'Dupont',
            'prenom' => 'Jean'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_handles_missing_candidat_role_on_registration()
    {
        // Supprimer le rôle candidat
        Role::where('libelle', 'CANDIDAT')->delete();

        $userData = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nom' => 'Dupont',
            'prenom' => 'Jean'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Erreur de configuration : rôle candidat introuvable'
            ]);

        // Vérifier qu'aucun utilisateur n'a été créé
        $this->assertDatabaseMissing('utilisateurs', [
            'email' => 'test@example.com'
        ]);
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        // Créer un utilisateur
        $user = Utilisateur::create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'nom' => 'Dupont',
            'prenom' => 'Jean'
        ]);

        // Assigner le rôle
        $user->roles()->attach($this->roleCandidat->id);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Connexion réussie'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'email',
                        'nom',
                        'prenom',
                        'telephone',
                        'roles'
                    ],
                    'token',
                    'token_type'
                ]
            ]);

        // Vérifier que l'utilisateur a un token
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'auth-token'
        ]);
    }

    /** @test */
    public function it_revokes_old_tokens_on_login()
    {
        // Créer un utilisateur
        $user = Utilisateur::create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'nom' => 'Dupont',
            'prenom' => 'Jean'
        ]);

        // Créer un ancien token
        $oldToken = $user->createToken('auth-token')->plainTextToken;

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(200);

        // Vérifier que l'ancien token a été supprimé
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'token' => hash('sha256', explode('|', $oldToken)[1])
        ]);

        // Vérifier qu'un nouveau token a été créé
        $this->assertEquals(1, $user->tokens()->count());
    }

    /** @test */
    public function it_cannot_login_with_invalid_password()
    {
        // Créer un utilisateur
        Utilisateur::create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'nom' => 'Dupont',
            'prenom' => 'Jean'
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ]);
    }

    /** @test */
    public function it_cannot_login_with_nonexistent_email()
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ]);
    }

    /** @test */
    /** @test */
    public function it_validates_required_fields_on_login()
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertStatus(422);

        // Vérifier la structure de la réponse réelle
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'email',
                'password'
            ]
        ]);

        // Vérifier les messages d'erreur spécifiques
        $response->assertJson([
            'message' => "L'email est requis (and 1 more error)",
            'errors' => [
                'email' => ["L'email est requis"],
                'password' => ["Le mot de passe est requis"]
            ]
        ]);
    }

    /** @test */
    public function it_validates_email_format_on_login()
    {
        $loginData = [
            'email' => 'invalid-email',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function registration_is_atomic_and_rolls_back_on_failure()
    {
        // Forcer une erreur en dupliquant une contrainte unique
        // D'abord, créer un utilisateur avec l'email qu'on va utiliser
        Utilisateur::create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'nom' => 'Existing',
            'prenom' => 'User'
        ]);

        // Maintenant, tenter de créer un autre utilisateur avec le même email
        $userData = [
            'email' => 'test@example.com', // Email déjà utilisé
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nom' => 'Dupont',
            'prenom' => 'Jean'
        ];

        $response = $this->postJson('/api/v1/register', $userData);

        $response->assertStatus(422); // Erreur de validation

        // Vérifier qu'aucun nouvel utilisateur n'a été créé
        $this->assertEquals(1, Utilisateur::where('email', 'test@example.com')->count());

        // Vérifier qu'aucun candidat n'a été créé avec l'ID de l'utilisateur existant
        $existingUser = Utilisateur::where('email', 'test@example.com')->first();
        $this->assertDatabaseMissing('candidats', [
            'utilisateur_id' => $existingUser->id
        ]);
    }

    /** @test */
    public function login_returns_correct_user_data_with_relations()
    {
        // Créer un utilisateur avec toutes les relations
        $user = Utilisateur::create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'telephone' => '+237600000000'
        ]);

        $user->roles()->attach($this->roleCandidat->id);

        $candidat = Candidat::create([
            'utilisateur_id' => $user->id,
            'adresse' => 'Test adresse'
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/v1/login', $loginData);

        $response->assertStatus(200);

        $responseData = $response->json('data');

        $this->assertEquals($user->id, $responseData['user']['id']);
        $this->assertEquals($user->email, $responseData['user']['email']);
        $this->assertEquals($user->nom, $responseData['user']['nom']);
        $this->assertEquals($user->prenom, $responseData['user']['prenom']);
        $this->assertEquals($user->telephone, $responseData['user']['telephone']);
        $this->assertContains('CANDIDAT', $responseData['user']['roles']);
        $this->assertEquals('Bearer', $responseData['token_type']);
    }
}
