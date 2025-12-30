<?php

namespace App\Http\Controllers;

use App\Models\Concours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConcoursController extends Controller
{
    /**
     * Afficher la liste des concours
     */
    public function index()
    {
        $concours = Concours::with('specification')->latest()->get();
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $concours = Concours::create($request->all());
        return response()->json($concours, 201);
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
