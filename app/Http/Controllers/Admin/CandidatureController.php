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
use App\Models\CandidatureRappel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CandidatureController extends Controller
{
    /**
     * Liste de toutes les candidatures
     */
    // public function index()
    // {
    //     $candidatures = Candidature::with([
    //         'candidat.utilisateur',
    //         'concours.concoursFilieres.filiere.departement.ecole',
    //         'centreDepot',
    //         'centreExamen',
    //         'historiques.etat'
    //     ])->orderByDesc('date_candidature')->get();

    //     return response()->json($candidatures);
    // }

    // Ajouter pagination + filtres
    public function index(Request $request)
    {
        $candidat = Auth::user()->candidat;

        if (!$candidat) {
            return response()->json([
                'message' => 'Candidat non trouvé'
            ], 404);
        }

        $query = Candidature::with([
            'candidat.utilisateur',
            'concours.concoursFilieres.filiere.departement.ecole',
            'centreDepot',
            'centreExamen',
            'historiques.etat'
        ])->where('candidat_id', $candidat->id);

        // Filtres
        if ($request->has('statut') && $request->statut !== 'all') {
            $query->where('statut', $request->statut);
        }

        if ($request->has('ecole_id')) {
            $query->whereHas('concours.concoursFilieres.filiere.departement', function ($q) use ($request) {
                $q->where('ecole_id', $request->ecole_id);
            });
        }

        // Tri
        $sortBy = $request->sort_by ?? 'date_candidature';
        $order = $request->order ?? 'desc';
        $query->orderBy($sortBy, $order);

        // Pagination
        $perPage = $request->per_page ?? 9;

        if ($request->has('page')) {
            $candidatures = $query->paginate($perPage);
        } else {
            $candidatures = $query->get();
        }

        return response()->json($candidatures);
    }

    /**
     * Détails d'une candidature
     */
    public function show(int $id)
    {
        $candidat = Auth::user()->candidat;

        $candidature = Candidature::with([
            'candidat.utilisateur',
            'concours.concoursFilieres.filiere.departement.ecole',
            'centreDepot',
            'centreExamen',
            'historiques.etat'
        ])->where('candidat_id', $candidat->id)
            ->findOrFail($id);

        return response()->json($candidature);
    }

    public function update(Request $request, int $id)
    {
        $candidat = Auth::user()->candidat;

        if (!$candidat) {
            return response()->json([
                'message' => 'Candidat non trouvé'
            ], 404);
        }

        // Vérifier que la candidature appartient au candidat authentifié
        $candidature = Candidature::where('candidat_id', $candidat->id)
            ->with(['candidat'])
            ->findOrFail($id);

        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            // Informations personnelles du candidat
            'adresse' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:100',
            'sexe' => 'nullable|in:M,F',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'region_origine' => 'nullable|string|max:255',
            'ville_origine' => 'nullable|string|max:255',
            'departement_origine' => 'nullable|string|max:255',
            'statut_matrimonial' => 'nullable|string|max:50',
            'handicap' => 'nullable|string|max:255',

            // Pièce d'identité
            'numero_cni' => 'nullable|string|max:50|unique:candidats,numero_cni,' . $candidat->id,
            'date_delivrance_cni' => 'nullable|date',

            // Informations familiales
            'nom_mere' => 'nullable|string|max:255',
            'telephone_mere' => 'nullable|string|max:20',
            'nom_pere' => 'nullable|string|max:255',
            'telephone_pere' => 'nullable|string|max:20',
            'nom_tuteur' => 'nullable|string|max:255',
            'telephone_tuteur' => 'nullable|string|max:20',

            // Informations scolaires
            'niveau_scolaire' => 'nullable|string|max:255',
            'filiere_origine' => 'nullable|string|max:255',
            'diplome_admission' => 'nullable|string|max:255',
            'mention' => 'nullable|string|max:100',
            'annee_diplome' => 'nullable|date',

        ], [
            'numero_cni.unique' => 'Ce numéro de CNI est déjà utilisé par un autre candidat',
            'sexe.in' => 'Le sexe doit être M (Masculin) ou F (Féminin)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // 1. Mettre à jour les informations du candidat
            $candidatData = [];

            // Informations personnelles
            $personalFields = [
                'adresse',
                'nationalite',
                'sexe',
                'date_naissance',
                'lieu_naissance',
                'region_origine',
                'ville_origine',
                'departement_origine',
                'statut_matrimonial',
                'handicap'
            ];

            foreach ($personalFields as $field) {
                if ($request->has($field)) {
                    $candidatData[$field] = $request->$field;
                }
            }

            // Pièce d'identité
            if ($request->has('numero_cni')) {
                $candidatData['numero_cni'] = $request->numero_cni;
            }
            if ($request->has('date_delivrance_cni')) {
                $candidatData['date_delivrance_cni'] = $request->date_delivrance_cni;
            }

            // Informations familiales
            $familyFields = [
                'nom_mere',
                'telephone_mere',
                'nom_pere',
                'telephone_pere',
                'nom_tuteur',
                'telephone_tuteur'
            ];

            foreach ($familyFields as $field) {
                if ($request->has($field)) {
                    $candidatData[$field] = $request->$field;
                }
            }

            // Informations scolaires
            $academicFields = [
                'niveau_scolaire',
                'filiere_origine',
                'diplome_admission',
                'mention',
                'annee_diplome'
            ];

            foreach ($academicFields as $field) {
                if ($request->has($field)) {
                    $candidatData[$field] = $request->$field;
                }
            }


            // Mettre à jour le candidat
            if (!empty($candidatData)) {
                $candidat->update($candidatData);
            }

            // 2. Mettre à jour les informations spécifiques à la candidature
            // $candidatureData = [];

            // $candidatureFields = [
            //     'centre_depot_id', 'centre_examen_id',
            //     'choix_filiere_1', 'choix_filiere_2', 'choix_filiere_3'
            // ];

            // foreach ($candidatureFields as $field) {
            //     if ($request->has($field)) {
            //         $candidatureData[$field] = $request->$field;
            //     }
            // }

            // Vérifier les centres si fournis
            if ($request->has('centre_depot_id')) {
                $centreDepot = Centre::find($request->centre_depot_id);
                if (!$centreDepot || $centreDepot->type_centre !== 'CENTRE_DE_DEPÔT') {
                    throw new \Exception('Centre de dépôt invalide');
                }
            }

            if ($request->has('centre_examen_id')) {
                $centreExamen = Centre::find($request->centre_examen_id);
                if (!$centreExamen || $centreExamen->type_centre !== 'CENTRE_EXAMEN') {
                    throw new \Exception('Centre d\'examen invalide');
                }
            }

            // Mettre à jour la candidature
            if (!empty($candidatureData)) {
                $candidature->update($candidatureData);

                // Si la candidature a été modifiée, ajouter un historique
                // $etatModifie = EtatCandidature::firstOrCreate(
                //     ['libelle_etat' => 'MODIFIEE'],
                //     ['description_etat' => 'Candidature modifiée par le candidat']
                // );

                // HistoriqueCandidature::create([
                //     'candidature_id' => $candidature->id,
                //     'etat_id' => $etatModifie->id,
                //     'date_etat' => now(),
                //     'commentaire' => 'Modification des informations par le candidat'
                // ]);

                // Mettre à jour la date de modification
                $candidature->touch();

                // Notification pour modification
                Notification::create([
                    'intitule' => 'Candidature modifiée',
                    'contenu' => 'Votre candidature a été mise à jour avec succès.',
                    'candidature_id' => $candidature->id
                ]);
            }

            DB::commit();

            // Recharger les relations
            $candidature->load([
                'candidat.utilisateur',
                'concours.concoursFilieres.filiere.departement.ecole',
                'centreDepot',
                'centreExamen',
                'historiques.etat'
            ]);

            return response()->json([
                'message' => 'Candidature mise à jour avec succès',
                'candidature' => $candidature
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la mise à jour de la candidature: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une candidature
     */
    public function destroy(int $id)
    {
        // L'admin peut supprimer n'importe quelle candidature
        $candidature = Candidature::with(['historiques', 'notifications'])
            ->findOrFail($id);

        DB::beginTransaction();

        try {
            // Optionnel: Vérifier si la candidature peut être supprimée
            // Par exemple, on pourrait interdire la suppression si elle est déjà validée
            $statutsNonSupprimables = ['APPROUVEE', 'REJETEE', 'VALIDEE'];

            if (in_array($candidature->statut, $statutsNonSupprimables)) {
                return response()->json([
                    'message' => 'Impossible de supprimer cette candidature. Les candidatures validées, approuvées ou rejetées ne peuvent pas être supprimées.',
                    'statut_actuel' => $candidature->statut
                ], 403);
            }

            // Option: Enregistrer une copie de la candidature avant suppression
            // (pour l'audit trail)
            Log::info('Suppression de candidature', [
                'admin_id' => Auth::id(),
                'candidature_id' => $candidature->id,
                'candidat_id' => $candidature->candidat_id,
                'concours_id' => $candidature->concours_id,
                'statut' => $candidature->statut,
                'date_suppression' => now()
            ]);

            // Supprimer les rappels associés
            CandidatureRappel::where('candidature_id', $candidature->id)->delete();

            // Supprimer les historiques
            HistoriqueCandidature::where('candidature_id', $candidature->id)->delete();

            // Supprimer les notifications
            Notification::where('candidature_id', $candidature->id)->delete();

            // Notifier le candidat de la suppression
            try {
                $candidature->load('candidat.utilisateur');

                // Optionnel: Envoyer un email au candidat
                Notification::create([
                    'intitule' => 'Candidature supprimée',
                    'contenu' => "Votre candidature pour le concours '{$candidature->concours->libelle_concours}' a été supprimée par l'administration.",
                    'candidature_id' => $candidature->id,
                    'user_id' => $candidature->candidat->utilisateur_id
                ]);

                // Vous pourriez aussi envoyer un email ici
                // Mail::to($candidature->candidat->utilisateur->email)->send(...)

            } catch (\Exception $e) {
                Log::warning('Erreur lors de la notification de suppression: ' . $e->getMessage());
            }

            // Supprimer la candidature
            $candidature->delete();

            DB::commit();

            return response()->json([
                'message' => 'Candidature supprimée avec succès',
                'deleted_id' => $id,
                'candidat_id' => $candidature->candidat_id // Pour information
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de la suppression de la candidature: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
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

    /**
     * Définir un rappel pour une candidature
     */
    public function definirRappel(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'date_rappel' => 'required|date|after_or_equal:today',
            'heure_rappel' => 'required|date_format:H:i',
        ], [
            'date_rappel.required' => 'La date du rappel est requise',
            'date_rappel.after_or_equal' => 'La date du rappel doit être aujourd\'hui ou dans le futur',
            'heure_rappel.required' => 'L\'heure du rappel est requise',
            'heure_rappel.date_format' => 'Le format de l\'heure doit être HH:MM',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $candidat = Auth::user()->candidat;

        $candidature = Candidature::where('candidat_id', $candidat->id)
            ->findOrFail($id);

        // Vérifier que la date du rappel est avant la date de l'examen
        $dateExamen = $candidature->concours->date_examen;
        if ($request->date_rappel >= $dateExamen) {
            return response()->json([
                'message' => 'La date du rappel doit être avant la date de l\'examen'
            ], 422);
        }

        // Supprimer l'ancien rappel s'il existe
        CandidatureRappel::where('candidature_id', $candidature->id)->delete();

        // Créer le nouveau rappel
        $rappel = CandidatureRappel::create([
            'candidature_id' => $candidature->id,
            'date_rappel' => $request->date_rappel,
            'heure_rappel' => $request->heure_rappel,
        ]);

        return response()->json([
            'message' => 'Rappel défini avec succès',
            'rappel' => $rappel
        ], 201);
    }
}
