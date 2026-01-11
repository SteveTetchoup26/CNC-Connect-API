<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Concours;
use App\Models\FraisConcours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ConcoursController extends Controller
{
    /**
     * Afficher la liste des concours
     */
    public function index()
    {
        try {
            $concours = Concours::with([
            // Charger uniquement la dernière session
            'sessions' => function($query) {
                $query
                    ->orderBy('sessions.created_at', 'desc')
                    ->limit(1);
            },
            // Charger les filières et l'école associée
            'concoursFilieres.filiere.departement.ecole',
            // Charger les informations de frais
            'fraisConcours'
            ])->latest()->get();

            return response()->json($concours);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des concours: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la récupération des concours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau concours
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'libelle_concours' => 'required|string|max:255',
            'date_limite' => 'required|date',
            'date_examen' => 'required|date|after:date_limite',

            // Filieres
            'filieres' => 'required|array|min:1',
            'filieres.*.id' => 'required|exists:filieres,id',
            'filieres.*.nbre_places' => 'required|integer|min:1',

            // Session unique
            'session_id' => 'required|exists:sessions,id',

            // Informations de frais
            'frais_concours' => 'required|array',
            'frais_concours.montant_frais' => 'required|numeric|min:0',
            'frais_concours.nom_banque' => 'required|string|max:255',
            'frais_concours.numero_compte' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // 1️⃣ Créer le concours
            $concours = Concours::create($request->only([
                'libelle_concours', 'date_limite', 'date_examen'
            ]));

            // 2️⃣ Associer les filières avec nombre de places
            foreach ($request->filieres as $filiere) {
                $concours->filieres()->attach($filiere['id'], ['nbre_places' => $filiere['nbre_places']]);
            }

            // 3️⃣ Associer la session unique
            $concours->sessions()->attach($request->session_id);

            // 4️⃣ Créer les informations de frais de concours
            FraisConcours::create([
                'montant_frais' => $request->frais_concours['montant_frais'],
                'nom_banque' => $request->frais_concours['nom_banque'],
                'numero_compte' => $request->frais_concours['numero_compte'],
                'concours_id' => $concours->id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Concours créé avec succès',
                'concours' => $concours->load('filieres', 'sessions', 'fraisConcours')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création du concours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un concours spécifique
     */
    public function show($id)
    {
        $concours = Concours::with([
            'specification',
            'fraisConcours',
            'sessions' => function($query) {
                $query
                    ->orderBy('sessions.created_at', 'desc')
                    ->limit(1);
            },
            'concoursFilieres' => function($query) {
                $query->with([
                    'filiere.departement.ecole',
                    'filiere.niveaux'
                ]);
            }
            ])->find($id);

        if (!$concours) {
            return response()->json(['message' => 'Concours non trouvé'], 404);
        }

        return response()->json($concours);
    }

    /**
     * Mettre à jour un concours
     */
    public function update(Request $request, $id)
    {
        $concours = Concours::with('fraisConcours')->find($id);

        if (!$concours) {
            return response()->json(['message' => 'Concours non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'libelle_concours' => 'sometimes|required|string|max:255',
            'date_limite' => 'sometimes|required|date',
            'date_examen' => 'sometimes|required|date',

            // Informations de frais (optionnelles pour la mise à jour)
            'frais_concours' => 'sometimes|array',
            'frais_concours.montant_frais' => 'sometimes|required_with:frais_concours|numeric|min:0',
            'frais_concours.nom_banque' => 'sometimes|required_with:frais_concours|string|max:255',
            'frais_concours.numero_compte' => 'sometimes|required_with:frais_concours|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // 1️⃣ Mettre à jour le concours
            $concours->update($request->only([
                'libelle_concours', 'date_limite', 'date_examen'
            ]));

            // 2️⃣ Mettre à jour ou créer les informations de frais
            if ($request->has('frais_concours')) {
                if ($concours->fraisConcours) {
                    // Mettre à jour les frais existants
                    $concours->fraisConcours->update([
                        'montant_frais' => $request->frais_concours['montant_frais'] ?? $concours->fraisConcours->montant_frais,
                        'nom_banque' => $request->frais_concours['nom_banque'] ?? $concours->fraisConcours->nom_banque,
                        'numero_compte' => $request->frais_concours['numero_compte'] ?? $concours->fraisConcours->numero_compte,
                    ]);
                } else {
                    // Créer de nouveaux frais
                    FraisConcours::create([
                        'montant_frais' => $request->frais_concours['montant_frais'],
                        'nom_banque' => $request->frais_concours['nom_banque'],
                        'numero_compte' => $request->frais_concours['numero_compte'],
                        'concours_id' => $concours->id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Concours mis à jour avec succès',
                'concours' => $concours->load('fraisConcours')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du concours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un concours
     */
    public function destroy($id)
    {
        $concours = Concours::find($id);

        if (!$concours) {
            return response()->json(['message' => 'Concours non trouvé'], 404);
        }

        DB::beginTransaction();

        try {
            // Supprimer les frais associés (cascade est configurée dans la migration)
            $concours->fraisConcours()->delete();

            // Supprimer le concours
            $concours->delete();

            DB::commit();

            return response()->json(['message' => 'Concours et ses frais associés supprimés avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la suppression du concours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les concours ouverts (date limite non dépassée)
     */
    public function getOpenConcours()
    {
        $concours = Concours::where('date_limite', '>=', now())
            ->with(['specification', 'fraisConcours'])
            ->latest()
            ->get();

        return response()->json($concours);
    }
}
