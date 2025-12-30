<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Epreuve;
use App\Models\Matiere;

class EpreuveController extends Controller
{
    /**
     * Liste de toutes les épreuves
     */
    public function index(): JsonResponse
    {
        $epreuves = Epreuve::with('matiere')->get();
        return response()->json($epreuves);
    }

    /**
     * Afficher une épreuve précise
     */
    public function show(int $id): JsonResponse
    {
        $epreuve = Epreuve::with('matiere')->findOrFail($id);
        return response()->json($epreuve);
    }

    /**
     * Créer une nouvelle épreuve
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'intitule' => 'required|string|max:255',
            'session' => 'required|date',
            'url_epreuve' => 'required|string|max:255',
            'matiere_id' => 'required|exists:matieres,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $epreuve = Epreuve::create($request->only('intitule', 'session', 'url_epreuve', 'matiere_id'));

        return response()->json([
            'message' => 'Épreuve créée avec succès',
            'epreuve' => $epreuve->load('matiere')
        ], 201);
    }

    /**
     * Mettre à jour une épreuve
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $epreuve = Epreuve::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'intitule' => 'sometimes|string|max:255',
            'session' => 'sometimes|date',
            'url_epreuve' => 'sometimes|string|max:255',
            'matiere_id' => 'sometimes|exists:matieres,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $epreuve->update($request->only('intitule', 'session', 'url_epreuve', 'matiere_id'));

        return response()->json([
            'message' => 'Épreuve mise à jour avec succès',
            'epreuve' => $epreuve->load('matiere')
        ]);
    }

    /**
     * Supprimer une épreuve
     */
    public function destroy(int $id): JsonResponse
    {
        Epreuve::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Épreuve supprimée avec succès'
        ]);
    }
}
