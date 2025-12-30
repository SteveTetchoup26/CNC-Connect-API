<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\EtatCandidature;
use App\Models\HistoriqueCandidature;
use App\Models\Notification;
use App\Models\Centre;
use App\Mail\CandidatureValideeMail;
use App\Mail\CandidatureRejeteeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CandidatureController extends Controller
{
    /**
     * Liste de toutes les candidatures
     */
    public function index()
    {
        $candidatures = Candidature::with([
            'candidat.utilisateur',
            'concours',
            'centreDepot',
            'centreExamen',
            'historiques.etat'
        ])->orderByDesc('date_candidature')->get();

        return response()->json($candidatures);
    }

    /**
     * Détails d'une candidature
     */
    public function show(int $id)
    {
        $candidature = Candidature::with([
            'candidat.utilisateur',
            'concours',
            'centreDepot',
            'centreExamen',
            'historiques.etat',
            'notifications'
        ])->findOrFail($id);

        return response()->json($candidature);
    }

    /**
     * Valider une candidature
     */
    public function valider(Request $request, int $id)
    {
        DB::beginTransaction();

        try {
            $candidature = Candidature::with(['candidat.utilisateur', 'concours'])
                ->findOrFail($id);

            // Générer code définitif
            $codeDef = $this->genererCodeDefinitif($candidature);

            $candidature->update([
                'code_candidat_def' => $codeDef,
                'date_validation' => now(),
            ]);

            // Etat APPROUVEE
            $etat = EtatCandidature::firstOrCreate(
                ['libelle_etat' => 'APPROUVEE'],
                ['description_etat' => 'Candidature approuvée']
            );

            HistoriqueCandidature::create([
                'candidature_id' => $candidature->id,
                'etat_id' => $etat->id,
                'date_etat' => now()
            ]);

            // Créer une notification
            Notification::create([
                'intitule' => 'Candidature validée',
                'contenu' => "Félicitations ! Votre candidature au concours '{$candidature->concours->libelle_concours}' a été validée. Votre code candidat définitif est : {$codeDef}. Vous pouvez télécharger votre fiche de candidature définitive.",
                'candidature_id' => $candidature->id
            ]);

            // Envoyer l'email de validation
            $downloadUrl = route('candidature.download.fiche', ['id' => $candidature->id]);

            try {
                Mail::to($candidature->candidat->utilisateur->email)->send(
                    new CandidatureValideeMail(
                        $candidature->candidat,
                        $candidature,
                        $candidature->concours,
                        $downloadUrl
                    )
                );
            } catch (\Exception $emailError) {
                Log::error('Erreur envoi email validation: ' . $emailError->getMessage());
            }

            DB::commit();

            return response()->json([
                'message' => 'Candidature validée avec succès',
                'code_definitif' => $codeDef,
                'candidature' => $candidature->load(['historiques.etat', 'notifications'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la validation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rejeter une candidature
     */
    public function rejeter(Request $request, int $id)
    {
        $request->validate([
            'motif_rejet' => 'required|string|min:10|max:1000'
        ]);

        DB::beginTransaction();

        try {
            $candidature = Candidature::with(['candidat.utilisateur', 'concours'])
                ->findOrFail($id);

            $candidature->update([
                'motif_rejet' => $request->motif_rejet
            ]);

            $etat = EtatCandidature::firstOrCreate(
                ['libelle_etat' => 'REJETEE'],
                ['description_etat' => 'Candidature rejetée']
            );

            HistoriqueCandidature::create([
                'candidature_id' => $candidature->id,
                'etat_id' => $etat->id,
                'date_etat' => now()
            ]);

            // Créer une notification
            Notification::create([
                'intitule' => 'Candidature non retenue',
                'contenu' => "Votre candidature au concours '{$candidature->concours->libelle_concours}' n'a malheureusement pas été retenue. Motif : {$request->motif_rejet}",
                'candidature_id' => $candidature->id
            ]);

            // Envoyer l'email de rejet
            try {
                Mail::to($candidature->candidat->utilisateur->email)->send(
                    new CandidatureRejeteeMail(
                        $candidature->candidat,
                        $candidature,
                        $candidature->concours,
                        $request->motif_rejet
                    )
                );
            } catch (\Exception $emailError) {
                Log::error('Erreur envoi email rejet: ' . $emailError->getMessage());
            }

            DB::commit();

            return response()->json([
                'message' => 'Candidature rejetée',
                'candidature' => $candidature->load(['historiques.etat', 'notifications'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors du rejet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affecter les centres (si modification nécessaire)
     */
    public function affecterCentres(Request $request, int $id)
    {
        $request->validate([
            'centre_depot_id' => 'nullable|exists:centres,id',
            'centre_examen_id' => 'nullable|exists:centres,id'
        ]);

        $candidature = Candidature::findOrFail($id);

        if ($request->centre_depot_id) {
            $centreDepot = Centre::find($request->centre_depot_id);
            if ($centreDepot->type_centre !== 'CENTRE_DE_DEPÔT') {
                return response()->json(['message' => 'Centre de dépôt invalide'], 422);
            }
        }

        if ($request->centre_examen_id) {
            $centreExamen = Centre::find($request->centre_examen_id);
            if ($centreExamen->type_centre !== 'CENTRE_EXAMEN') {
                return response()->json(['message' => 'Centre d\'examen invalide'], 422);
            }
        }

        $candidature->update($request->only(['centre_depot_id', 'centre_examen_id']));

        return response()->json([
            'message' => 'Centres mis à jour avec succès',
            'candidature' => $candidature->load(['centreDepot', 'centreExamen'])
        ]);
    }

    /**
     * Génération du code définitif
     */
    private function genererCodeDefinitif(Candidature $candidature): string
    {
        do {
            $code = 'DEF-' .
                str_pad($candidature->concours_id, 4, '0', STR_PAD_LEFT) . '-' .
                str_pad($candidature->id, 6, '0', STR_PAD_LEFT) . '-' .
                strtoupper(Str::random(4));
        } while (Candidature::where('code_candidat_def', $code)->exists());

        return $code;
    }
}
