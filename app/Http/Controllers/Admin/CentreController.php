<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Centre;
use Illuminate\Http\Request;

class CentreController extends Controller
{
    /**
     * GET /api/centres
     * Tous les centres
     */
    public function index()
    {
        return response()->json(
            Centre::with('region')->get()
        );
    }

    /**
     * GET /api/centres/type/{type}
     * Centres par type (DEPOT | EXAMEN)
     */
    public function byType(string $type)
    {
        if (!in_array($type, ['CENTRE_DE_DEPÔT', 'CENTRE_EXAMEN'])) {
            return response()->json([
                'message' => 'Type de centre invalide'
            ], 400);
        }

        $centres = Centre::where('type_centre', $type)
            ->with('region')
            ->get();

        return response()->json($centres);
    }

    /**
     * GET /api/centres/type/{type}/region/{regionId}
     * Centres par type + région
     */
    public function byTypeAndRegion(string $type, int $regionId)
    {
        $centres = Centre::where('type_centre', $type)
            ->where('region_id', $regionId)
            ->with('region')
            ->get();

        return response()->json($centres);
    }
}
