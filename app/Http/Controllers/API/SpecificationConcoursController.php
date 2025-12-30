<?php

namespace App\Http\Controllers;

use App\Models\SpecificationConcours;
use App\Models\Concours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpecificationConcoursController extends Controller
{
    /**
     * Afficher toutes les spécifications
     */
    public function index()
    {
        $specifications = SpecificationConcours::with('concours')->get();
        return response()->json($specifications);
    }

    /**
     * Créer une nouvelle spécification pour un concours
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'concours_id' => 'required|integer|exists:concours,id',
            'desc_infos_concours' => 'nullable|string',
            'carte_nationale_identite' => 'boolean',
            'diplomes' => 'boolean',
            'certificat_nationalite' => 'boolean',
            'releve_notes' => 'boolean',
            'acte_naissance' => 'boolean',
            'photo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier si une spécification existe déjà pour ce concours
        $existingSpec = SpecificationConcours::where('concours_id', $request->concours_id)->first();
        if ($existingSpec) {
            return response()->json([
                'message' => 'Une spécification existe déjà pour ce concours'
            ], 409);
        }

        $specification = SpecificationConcours::create($request->all());
        return response()->json($specification, 201);
    }

    /**
     * Afficher une spécification spécifique
     */
    public function show($id)
    {
        $specification = SpecificationConcours::with('concours')->find($id);

        if (!$specification) {
            return response()->json(['message' => 'Spécification non trouvée'], 404);
        }

        return response()->json($specification);
    }

    /**
     * Afficher la spécification d'un concours par son ID
     */
    public function showByConcours($concours_id)
    {
        $specification = SpecificationConcours::where('concours_id', $concours_id)
            ->with('concours')
            ->first();

        if (!$specification) {
            return response()->json(['message' => 'Spécification non trouvée pour ce concours'], 404);
        }

        return response()->json($specification);
    }

    /**
     * Mettre à jour une spécification
     */
    public function update(Request $request, $id)
    {
        $specification = SpecificationConcours::find($id);

        if (!$specification) {
            return response()->json(['message' => 'Spécification non trouvée'], 404);
        }

        $validator = Validator::make($request->all(), [
            'concours_id' => 'sometimes|required|integer|exists:concours,id',
            'desc_infos_concours' => 'nullable|string',
            'carte_nationale_identite' => 'boolean',
            'diplomes' => 'boolean',
            'certificat_nationalite' => 'boolean',
            'releve_notes' => 'boolean',
            'acte_naissance' => 'boolean',
            'photo' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $specification->update($request->all());
        return response()->json($specification);
    }

    /**
     * Supprimer une spécification
     */
    public function destroy($id)
    {
        $specification = SpecificationConcours::find($id);

        if (!$specification) {
            return response()->json(['message' => 'Spécification non trouvée'], 404);
        }

        $specification->delete();
        return response()->json(['message' => 'Spécification supprimée avec succès']);
    }

    /**
     * Obtenir les documents requis pour un concours
     */
    public function getRequiredDocuments($concours_id)
    {
        $specification = SpecificationConcours::where('concours_id', $concours_id)->first();

        if (!$specification) {
            return response()->json(['message' => 'Spécification non trouvée'], 404);
        }

        $requiredDocs = [];
        $documents = [
            'carte_nationale_identite' => 'Carte Nationale d\'Identité',
            'diplomes' => 'Diplômes',
            'certificat_nationalite' => 'Certificat de Nationalité',
            'releve_notes' => 'Relevé de Notes',
            'acte_naissance' => 'Acte de Naissance',
            'photo' => 'Photo d\'identité',
        ];

        foreach ($documents as $key => $label) {
            if ($specification->$key) {
                $requiredDocs[] = $label;
            }
        }

        return response()->json([
            'concours_id' => $concours_id,
            'documents_requis' => $requiredDocs,
            'description' => $specification->desc_infos_concours
        ]);
    }
}
