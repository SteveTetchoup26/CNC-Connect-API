<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\FichePaiement;
use App\Models\EtatCandidature;
use App\Models\HistoriqueCandidature;
use App\Models\Notification;
use App\Models\Concours;
use App\Mail\CandidatureSuccessMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CandidatController extends Controller
{
    /**
     * Inscription d'un candidat à un concours
     */
    public function inscriptionConcours(Request $request)
    {
        // Récupérer l'utilisateur authentifié via Sanctum
        $utilisateur = $request->user();

        if (!$utilisateur) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $validator = Validator::make($request->all(), [
            // Concours
            'concours_id' => 'required|integer|exists:concours,id',

            // Informations personnelles
            'adresse' => 'required|string|max:255',
            'nationalite' => 'required|string|max:100',
            'sexe' => 'required|in:M,F',
            'age' => 'required|integer|min:16|max:100',
            'date_naissance' => 'required|date|before:today',
            'lieu_naissance' => 'required|string|max:255',
            'region_origine' => 'required|string|max:255',
            'ville_origine' => 'required|string|max:255',
            'departement_origine' => 'required|string|max:255',
            'statut_matrimonial' => 'required|string|max:50',
            'handicap' => 'nullable|string|max:255',

            // Pièce d'identité
            'numero_cni' => 'required|string|unique:candidats,numero_cni|max:50',
            'date_delivrance_cni' => 'required|date|before_or_equal:today',

            // Informations familiales
            'nom_mere' => 'required|string|max:255',
            'telephone_mere' => 'required|string|max:20',
            'nom_pere' => 'nullable|string|max:255',
            'telephone_pere' => 'nullable|string|max:20',
            'nom_tuteur' => 'nullable|string|max:255',
            'telephone_tuteur' => 'nullable|string|max:20',

            // Informations scolaires
            'niveau_scolaire' => 'required|string|max:100',
            'filiere_origine' => 'required|string|max:100',
            'diplome_admission' => 'required|string|max:100',
            'mention' => 'nullable|string|max:50',
            'annee_diplome' => 'required|integer|min:1950|max:' . now()->year,

            // Paiement
            'num_recu' => 'required|string|unique:candidats,num_recu|max:100',
            'fiche_paiement' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',

            // Centres
            'centre_depot_id' => 'required|integer|exists:centres,id',
            'centre_examen_id' => 'required|integer|exists:centres,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier si le candidat existe déjà pour l'utilisateur authentifié
        $candidatExistant = Candidat::where('utilisateur_id', $utilisateur->id)->first();

        // Vérifier si l'utilisateur est déjà inscrit à ce concours
        if ($candidatExistant) {
            $candidatureExistante = Candidature::where('candidat_id', $candidatExistant->id)
                ->where('concours_id', $request->concours_id)
                ->first();

            if ($candidatureExistante) {
                return response()->json([
                    'message' => 'Vous êtes déjà inscrit à ce concours',
                    'candidature' => $candidatureExistante
                ], 409);
            }
        }

        DB::beginTransaction();

        try {
            // 1. Créer ou mettre à jour le candidat
            $candidatData = $request->except(['fiche_paiement', 'concours_id', 'centre_depot_id', 'centre_examen_id']);
            $candidatData['utilisateur_id'] = $utilisateur->id; // Ajouter l'ID de l'utilisateur authentifié

            if ($candidatExistant) {
                $candidat = $candidatExistant;
                $candidat->update($candidatData);
            } else {
                $candidat = Candidat::create($candidatData);
            }

            // 2. Uploader la fiche de paiement
            if ($request->hasFile('fiche_paiement')) {
                $file = $request->file('fiche_paiement');
                $filename = 'paiement_' . $candidat->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('fiches_paiement', $filename, 'public');

                FichePaiement::create([
                    'fichier_url' => $path,
                    'candidat_id' => $candidat->id
                ]);
            }

            // 3. Générer le code temporaire
            $codeTemporaire = $this->genererCodeTemporaire($request->concours_id, $candidat->id);

            // 4. Créer la candidature (sans QR code pour l'instant)
            $candidature = Candidature::create([
                'candidat_id' => $candidat->id,
                'concours_id' => $request->concours_id,
                'code_candidat_temp' => $codeTemporaire,
                'date_candidature' => now(),
                'date_inscription' => now()->toDateString(),
                'centre_depot_id' => $request->centre_depot_id,
                'centre_examen_id' => $request->centre_examen_id,
            ]);

            // 5. Récupérer ou créer l'état "EN_ATTENTE"
            $etatEnAttente = EtatCandidature::firstOrCreate(
                ['libelle_etat' => 'EN_ATTENTE'],
                ['description_etat' => 'Candidature en attente de validation']
            );

            // 6. Créer l'historique de candidature
            HistoriqueCandidature::create([
                'date_etat' => now(),
                'etat_id' => $etatEnAttente->id,
                'candidature_id' => $candidature->id
            ]);

            // 7. Générer une notification
            $concours = Concours::find($request->concours_id);
            Notification::create([
                'intitule' => 'Inscription réussie au concours',
                'contenu' => "Votre inscription au concours '{$concours->libelle_concours}' a été enregistrée avec succès. Votre code temporaire est : {$codeTemporaire}. Votre dossier est en attente de validation.",
                'candidature_id' => $candidature->id
            ]);

            $downloadUrl = route('candidature.download.fiche', ['id' => $candidature->id]);

            // 8. Envoyer l'email avec la fiche PDF
            try {
                Mail::to($utilisateur->email)->send(
                    new CandidatureSuccessMail($candidat, $candidature, $concours, $downloadUrl)
                );
            } catch (\Exception $emailError) {
                // Log l'erreur mais continue le processus
                Log::error('Erreur envoi email: ' . $emailError->getMessage());
            }

            DB::commit();

            return response()->json([
                'message' => 'Inscription réussie',
                'candidat' => $candidat,
                'candidature' => $candidature->load(['concours', 'centreDepot', 'centreExamen']),
                'code_temporaire' => $codeTemporaire,
                'statut' => 'EN_ATTENTE',
                'download_url' => $downloadUrl
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de l\'inscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer un code temporaire unique
     */
    private function genererCodeTemporaire($concours_id, $candidat_id)
    {
        $prefix = 'TEMP';
        $concoursCode = str_pad($concours_id, 4, '0', STR_PAD_LEFT);
        $candidatCode = str_pad($candidat_id, 6, '0', STR_PAD_LEFT);
        $random = strtoupper(Str::random(4));

        return "{$prefix}-{$concoursCode}-{$candidatCode}-{$random}";
    }

    /**
     * Afficher les informations du candidat authentifié
     */
    public function show(Request $request)
    {
        // Récupérer l'utilisateur authentifié
        $utilisateur = $request->user();

        if (!$utilisateur) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $candidat = Candidat::where('utilisateur_id', $utilisateur->id)
            ->with(['utilisateur', 'candidatures.concours', 'fichePaiements'])
            ->first();

        if (!$candidat) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }

        return response()->json($candidat);
    }

    /**
     * Mettre à jour les informations du candidat authentifié
     */
    public function update(Request $request)
    {
        // Récupérer l'utilisateur authentifié
        $utilisateur = $request->user();

        if (!$utilisateur) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $candidat = Candidat::where('utilisateur_id', $utilisateur->id)->first();

        if (!$candidat) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'adresse' => 'sometimes|string|max:255',
            'nationalite' => 'sometimes|string|max:100',
            'sexe' => 'sometimes|in:M,F',
            'age' => 'sometimes|integer|min:16|max:100',
            'telephone_mere' => 'sometimes|string|max:20',
            'telephone_pere' => 'sometimes|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidat->update($request->all());

        return response()->json([
            'message' => 'Informations mises à jour avec succès',
            'candidat' => $candidat
        ]);
    }

    /**
     * Obtenir toutes les candidatures de l'utilisateur authentifié
     */
    public function getCandidatures(Request $request)
    {
        // Récupérer l'utilisateur authentifié
        $utilisateur = $request->user();

        if (!$utilisateur) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $candidat = Candidat::where('utilisateur_id', $utilisateur->id)->first();

        if (!$candidat) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }

        $candidatures = Candidature::where('candidat_id', $candidat->id)
            ->with(['concours', 'historiques.etat', 'notifications', 'centreDepot', 'centreExamen'])
            ->get();

        return response()->json([
            'candidat' => $candidat,
            'candidatures' => $candidatures
        ]);
    }

    /**
     * Obtenir le profil complet de l'utilisateur authentifié
     */
    public function getProfilByUtilisateur(Request $request)
    {
        // Récupérer l'utilisateur authentifié
        $utilisateur = $request->user();

        if (!$utilisateur) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $candidat = Candidat::where('utilisateur_id', $utilisateur->id)
            ->with([
                'candidatures.concours',
                'candidatures.historiques.etat',
                'candidatures.notifications',
                'candidatures.centreDepot',
                'candidatures.centreExamen',
                'fichePaiements'
            ])
            ->first();

        if (!$candidat) {
            return response()->json(['message' => 'Aucun profil candidat trouvé pour cet utilisateur'], 404);
        }

        return response()->json($candidat);
    }

    /**
     * Télécharger la fiche de paiement de l'utilisateur authentifié
     */
    public function downloadFichePaiement(Request $request)
    {
        // Récupérer l'utilisateur authentifié
        $utilisateur = $request->user();

        if (!$utilisateur) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        // Vérifier si l'utilisateur a un profil candidat
        $candidat = Candidat::where('utilisateur_id', $utilisateur->id)->first();

        if (!$candidat) {
            return response()->json(['message' => 'Aucun profil candidat trouvé'], 404);
        }

        // Récupérer la fiche de paiement du candidat
        $fichePaiement = FichePaiement::where('candidat_id', $candidat->id)
            ->latest()
            ->first();

        if (!$fichePaiement) {
            return response()->json(['message' => 'Fiche de paiement non trouvée'], 404);
        }

        if (!Storage::disk('public')->exists($fichePaiement->fichier_url)) {
            return response()->json(['message' => 'Fichier introuvable'], 404);
        }

        return Storage::disk('public')->download($fichePaiement->fichier_url);
    }

    /**
     * Supprimer le profil candidat de l'utilisateur authentifié
     */
    public function destroy(Request $request)
    {
        // Récupérer l'utilisateur authentifié
        $utilisateur = $request->user();

        if (!$utilisateur) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        $candidat = Candidat::where('utilisateur_id', $utilisateur->id)->first();

        if (!$candidat) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }

        // Supprimer les fichiers de paiement
        $fichePaiements = FichePaiement::where('candidat_id', $candidat->id)->get();
        foreach ($fichePaiements as $fiche) {
            if (Storage::disk('public')->exists($fiche->fichier_url)) {
                Storage::disk('public')->delete($fiche->fichier_url);
            }
        }

        $candidat->delete();

        return response()->json(['message' => 'Candidat supprimé avec succès']);
    }

    /**
     * Télécharger la fiche de candidature en PDF
     */
    public function downloadFicheCandidature($id)
    {
        $candidature = Candidature::with([
            'candidat.utilisateur',
            'concours',
            'centreDepot',
            'centreExamen'
        ])->find($id);

        if (!$candidature) {
            return response()->json(['message' => 'Candidature non trouvée'], 404);
        }

        // Récupérer l'école via la relation Concours -> Filiere -> Departement -> Ecole
        $ecole = null;
        $premiereFiliere = $candidature->concours->filieres()->first();
        if ($premiereFiliere && $premiereFiliere->departement) {
            $ecole = $premiereFiliere->departement->ecole;
        }

        // Générer le QR Code
        $downloadUrl = route('candidature.download.fiche', ['id' => $candidature->id]);
        $qrCode = QrCode::format('svg')->size(300)->generate($downloadUrl);
        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCode);

        // Préparer les données pour la vue
        $data = [
            'candidature' => $candidature,
            'candidat' => $candidature->candidat,
            'utilisateur' => $candidature->candidat->utilisateur,
            'concours' => $candidature->concours,
            'centreDepot' => $candidature->centreDepot,
            'centreExamen' => $candidature->centreExamen,
            'ecole' => $ecole,
            'qrCodeImage' => $qrCodeBase64,
        ];

        // Générer le PDF
        $pdf = Pdf::loadView('pdf.fiche_candidature', $data);
        $pdf->setPaper('a4', 'portrait');

        // Télécharger le PDF
        $filename = 'fiche_candidature_' . $candidature->code_candidat_temp . '.pdf';
        return $pdf->download($filename);
    }
}
