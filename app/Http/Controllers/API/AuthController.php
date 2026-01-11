<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\Candidat;
use App\Models\Utilisateur;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Info(
 *     title="API Gestion Concours",
 *     version="1.0.0",
 *     description="Documentation de l'API pour la gestion des concours",
 *     @OA\Contact(
 *         email="support@concours.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Serveur de développement"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Inscription d'un nouvel utilisateur",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="nom", type="string", example="Dupont"),
     *             @OA\Property(property="prenom", type="string", example="Jean"),
     *             @OA\Property(property="telephone", type="string", example="+237600000000")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inscription réussie"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="email", type="string", example="user@example.com"),
     *                     @OA\Property(property="nom", type="string", example="Dupont"),
     *                     @OA\Property(property="prenom", type="string", example="Jean"),
     *                     @OA\Property(property="telephone", type="string", example="+237600000000"),
     *                     @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|abc123xyz..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            // Utiliser une transaction pour garantir la cohérence
            DB::beginTransaction();

            // Créer l'utilisateur
            $utilisateur = Utilisateur::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'telephone' => $request->telephone,
            ]);

            // Récupérer le rôle "candidat"
            $roleCandidat = Role::where('libelle', 'CANDIDAT')->first();

            if (!$roleCandidat) {
                DB::rollBack();
                Log::error('Rôle candidat non trouvé lors de l\'inscription');

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de configuration : rôle candidat introuvable',
                ], 500);
            }

            // Assigner le rôle candidat à l'utilisateur
            $utilisateur->roles()->attach($roleCandidat->id);

            $candidat = Candidat::create([
                'utilisateur_id' => $utilisateur->id,
                'adresse' => null,
                'nationalite' => null,
                'sexe' => null,
                'date_naissance' => null,
                'lieu_naissance' => null,
                'region_origine' => null,
                'ville_origine' => null,
                'departement_origine' => null,
                'nom_mere' => null,
                'telephone_mere' => null,
                'nom_pere' => null,
                'telephone_pere' => null,
                'niveau_scolaire' => null,
            ]);

            $expiration = Carbon::now()->addDay();

            // Générer un nouveau token
            $token = $utilisateur->createToken('auth-token', ["*"],$expiration)->plainTextToken;

            // Charger les relations pour la réponse
            $utilisateur->load('roles');

            // Valider la transaction
            DB::commit();

            // Logger l'inscription
            Log::info('Nouvel utilisateur inscrit', [
                'user_id' => $utilisateur->id,
                'email' => $utilisateur->email,
                'role' => 'candidat',
                'candidat_id' => $candidat->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Inscription réussie',
                // 'data' => [
                //     'user' => [
                //         'id' => $utilisateur->id,
                //         'email' => $utilisateur->email,
                //         'nom' => $utilisateur->nom,
                //         'prenom' => $utilisateur->prenom,
                //         'telephone' => $utilisateur->telephone,
                //         'roles' => $utilisateur->roles->pluck('libelle_role'),
                //         'candidat' => $utilisateur->candidat,
                //         'created_at' => $utilisateur->created_at,
                //     ],
                // ],
            ], 201);

        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            DB::rollBack();

            Log::error('Erreur lors de l\'inscription', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'inscription',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Connexion d'un utilisateur",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Connexion réussie"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="email", type="string", example="user@example.com"),
     *                     @OA\Property(property="nom", type="string", example="Dupont"),
     *                     @OA\Property(property="prenom", type="string", example="Jean"),
     *                     @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="candidat", type="object", nullable=true),
     *                     @OA\Property(property="administrateur", type="object", nullable=true)
     *                 ),
     *                 @OA\Property(property="token", type="string", example="2|xyz789abc..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants incorrects",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Email ou mot de passe incorrect")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Vérifier les identifiants
            if (!Auth::attempt($request->only('email', 'password'))) {
                Log::warning('Tentative de connexion échouée', ['email' => $request->email]);

                return response()->json([
                    'success' => false,
                    'message' => 'Email ou mot de passe incorrect',
                ], 401);
            }

            $utilisateur = Auth::user();

            // Révoquer tous les anciens tokens
            // $utilisateur->tokens()->delete();
            $old_token = DB::table('personal_access_tokens')
                ->where('tokenable_id', $utilisateur->id)
                ->get();

            if($old_token->count() > 0) {
                DB::table('personal_access_tokens')
                    ->where('tokenable_id', $utilisateur->id)
                    ->delete();
            }

            $expiration = Carbon::now()->addDay();

            // Générer un nouveau token
            $token = $utilisateur->createToken('auth-token', ["*"],$expiration)->plainTextToken;

            // Charger les relations
            $utilisateur->load(['roles', 'candidat', 'administrateur']);

            Log::info('Utilisateur connecté', ['user_id' => $utilisateur->id, 'email' => $utilisateur->email]);

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => [
                    'user' => [
                        'id' => $utilisateur->id,
                        'email' => $utilisateur->email,
                        'nom' => $utilisateur->nom,
                        'prenom' => $utilisateur->prenom,
                        'telephone' => $utilisateur->telephone,
                        'roles' => $utilisateur->roles->pluck('libelle'),
                        // 'candidat' => $utilisateur->candidat,
                        // 'administrateur' => $utilisateur->administrateur,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la connexion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la connexion',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Déconnexion de l'utilisateur",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Révoquer le token actuel
            $request->user()->currentAccessToken()->delete();

            Log::info('Utilisateur déconnecté', ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la déconnexion', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la déconnexion',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/user",
     *     summary="Récupérer l'utilisateur authentifié",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations de l'utilisateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="email", type="string", example="user@example.com"),
     *                     @OA\Property(property="nom", type="string", example="Dupont"),
     *                     @OA\Property(property="prenom", type="string", example="Jean"),
     *                     @OA\Property(property="telephone", type="string", example="+237600000000"),
     *                     @OA\Property(property="roles", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="candidat", type="object", nullable=true),
     *                     @OA\Property(property="administrateur", type="object", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $utilisateur = $request->user();

            // Charger les relations
            $utilisateur->load(['roles', 'candidat', 'administrateur']);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $utilisateur->id,
                        'email' => $utilisateur->email,
                        'nom' => $utilisateur->nom,
                        'prenom' => $utilisateur->prenom,
                        'telephone' => $utilisateur->telephone,
                        'roles' => $utilisateur->roles->pluck('libelle'),
                        'candidat' => $utilisateur->candidat,
                        'administrateur' => $utilisateur->administrateur,
                        // 'created_at' => $utilisateur->created_at,
                        // 'updated_at' => $utilisateur->updated_at,
                    ],
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'utilisateur', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/refresh-token",
     *     summary="Rafraîchir le token d'authentification",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token rafraîchi"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="3|new_token_here..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $utilisateur = $request->user();

            // Révoquer le token actuel
            $request->user()->currentAccessToken()->delete();

            // Générer un nouveau token
            $token = $utilisateur->createToken('auth-token')->plainTextToken;

            Log::info('Token rafraîchi', ['user_id' => $utilisateur->id]);

            return response()->json([
                'success' => true,
                'message' => 'Token rafraîchi',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors du rafraîchissement du token', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue',
            ], 500);
        }
    }
}
