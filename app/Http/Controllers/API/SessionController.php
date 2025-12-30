<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Session;
use App\Models\EtatSession;
use App\Models\HistoriqueSession;

class SessionController extends Controller
{
    /**
     * Liste de toutes les sessions
     */
    public function index(): JsonResponse
    {
        $sessions = Session::with(['historique.etat'])->get();
        return response()->json($sessions);
    }

    /**
     * Afficher une session précise
     */
    public function show(int $id): JsonResponse
    {
        $session = Session::with(['historique.etat'])->findOrFail($id);
        return response()->json($session);
    }

    /**
     * Créer une nouvelle session
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'libelle_session' => 'required|string|max:255',
            'description_session' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $session = Session::create($request->only('libelle_session', 'description_session'));

        // Initialiser l'état par défaut OUVERTE
        $etatOuverte = EtatSession::firstOrCreate(
            ['libelle_etat' => 'OUVERTE'],
            ['description_etat' => 'Session ouverte']
        );

        HistoriqueSession::create([
            'session_id' => $session->id,
            'etat_id' => $etatOuverte->id,
            'date_etat' => now()
        ]);

        return response()->json([
            'message' => 'Session créée avec succès',
            'session' => $session->load('historique.etat')
        ], 201);
    }

    /**
     * Mettre à jour une session
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $session = Session::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'libelle_session' => 'sometimes|string|max:255',
            'description_session' => 'sometimes|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $session->update($request->only('libelle_session', 'description_session'));

        return response()->json([
            'message' => 'Session mise à jour avec succès',
            'session' => $session
        ]);
    }

    /**
     * Changer l'état d'une session (OUVERTE / FERMEE)
     */
    public function changerEtat(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'etat_id' => 'required|exists:etat_sessions,id'
        ]);

        DB::transaction(function () use ($request, $id) {
            HistoriqueSession::create([
                'session_id' => $id,
                'etat_id' => $request->etat_id,
                'date_etat' => now()
            ]);
        });

        return response()->json([
            'message' => 'État de la session mis à jour'
        ]);
    }

    /**
     * Supprimer une session
     */
    public function destroy(int $id): JsonResponse
    {
        Session::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Session supprimée avec succès'
        ]);
    }
}
