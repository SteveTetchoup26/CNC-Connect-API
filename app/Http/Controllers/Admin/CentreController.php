<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Centre;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CentreController extends Controller
{
    /**
     * GET /api/admin/centres
     */
    public function index()
    {
        return response()->json(
            Centre::with('region')->orderBy('libelle_centre')->get()
        );
    }

    /**
     * POST /api/admin/centres
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle_centre' => 'required|string|max:255',
            'type_centre' => [
                'required',
                Rule::in(['CENTRE_DE_DEPÔT', 'CENTRE_EXAMEN'])
            ],
            'ville_centre' => 'required|string|max:255',
            'capacite' => 'nullable|integer|min:1',
            'region_id' => 'required|exists:regions,id',
        ]);

        $centre = Centre::create($validated);

        return response()->json([
            'message' => 'Centre créé avec succès',
            'centre' => $centre->load('region')
        ], 201);
    }

    /**
     * GET /api/admin/centres/{id}
     */
    public function show(int $id)
    {
        $centre = Centre::with('region')->findOrFail($id);

        return response()->json($centre);
    }

    /**
     * PUT /api/admin/centres/{id}
     */
    public function update(Request $request, int $id)
    {
        $centre = Centre::findOrFail($id);

        $validated = $request->validate([
            'libelle_centre' => 'required|string|max:255',
            'type_centre' => [
                'required',
                Rule::in(['CENTRE_DE_DEPÔT', 'CENTRE_EXAMEN'])
            ],
            'ville_centre' => 'required|string|max:255',
            'capacite' => 'required|integer|min:1',
            'region_id' => 'required|exists:regions,id',
        ]);

        $centre->update($validated);

        return response()->json([
            'message' => 'Centre mis à jour avec succès',
            'centre' => $centre->load('region')
        ]);
    }

    /**
     * DELETE /api/admin/centres/{id}
     */
    public function destroy(int $id)
    {
        $centre = Centre::findOrFail($id);
        $centre->delete();

        return response()->json([
            'message' => 'Centre supprimé avec succès'
        ]);
    }

    /**
     * GET /api/admin/centres/type/{type}
     */
    public function byType(string $type)
    {
        if (!in_array($type, ['CENTRE_DE_DEPÔT', 'CENTRE_EXAMEN'])) {
            return response()->json([
                'message' => 'Type de centre invalide'
            ], 400);
        }

        return response()->json(
            Centre::where('type_centre', $type)
                ->with('region')
                ->get()
        );
    }

    /**
     * GET /api/admin/centres/type/{type}/region/{regionId}
     */
    public function byTypeAndRegion(string $type, int $regionId)
    {
        return response()->json(
            Centre::where('type_centre', $type)
                ->where('region_id', $regionId)
                ->with('region')
                ->get()
        );
    }
}
