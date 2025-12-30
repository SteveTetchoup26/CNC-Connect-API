<?php

namespace App\Http\Controllers;

use App\Models\Concours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConcoursController extends Controller
{
    /**
     * Afficher la liste des concours
     */
    public function index()
    {
        $concours = Concours::with([
            // Charger uniquement la dernière session
            'sessions' => function($query) {
                $query->latest()->limit(1);
            },
            // Charger les filières et l'école associée
            'concoursFilieres.filiere.departement.ecole'
        ])->latest()->get();

        return response()->json($concours);
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
            'frais_inscription' => 'required|numeric|min:0',

            // Filieres
            'filieres' => 'required|array|min:1',
            'filieres.*.id' => 'required|exists:filieres,id',
            'filieres.*.nbre_places' => 'required|integer|min:1',

            // Session unique
            'session_id' => 'required|exists:sessions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // 1️⃣ Créer le concours
            $concours = Concours::create($request->only([
                'libelle_concours', 'date_limite', 'date_examen', 'frais_inscription'
            ]));

            // 2️⃣ Associer les filières avec nombre de places
            foreach ($request->filieres as $filiere) {
                $concours->filieres()->attach($filiere['id'], ['nbre_places' => $filiere['nbre_places']]);
            }

            // 3️⃣ Associer la session unique
            $concours->sessions()->attach($request->session_id);

            DB::commit();

            return response()->json([
                'message' => 'Concours créé avec succès',
                'concours' => $concours->load('filieres', 'sessions')
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
        $concours = Concours::with('specification')->find($id);

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
        $concours = Concours::find($id);

        if (!$concours) {
            return response()->json(['message' => 'Concours non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'libelle_concours' => 'sometimes|required|string|max:255',
            'date_limite' => 'sometimes|required|date',
            'date_examen' => 'sometimes|required|date',
            'frais_inscription' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $concours->update($request->all());
        return response()->json($concours);
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

        $concours->delete();
        return response()->json(['message' => 'Concours supprimé avec succès']);
    }

    /**
     * Obtenir les concours ouverts (date limite non dépassée)
     */
    public function getOpenConcours()
    {
        $concours = Concours::where('date_limite', '>=', now())
            ->with('specification')
            ->latest()
            ->get();

        return response()->json($concours);
    }
}
