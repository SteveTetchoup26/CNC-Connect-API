<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ecole;
use App\Models\Departement;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EcoleController extends Controller
{
    /**
     * Création complète via Stepper
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ecole.libelle_ecole' => 'required|string',
            'ecole.code_ecole' => 'required|string|unique:ecoles,code_ecole',
            'ecole.localisation' => 'required|string',
            'ecole.email_ecole' => 'required|email|unique:ecoles,email_ecole',
            'ecole.logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'ecole.banniere' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'ecole.bp_ecole' => 'nullable|string',
            'ecole.siteweb_ecole' => 'nullable|url',
            'ecole.devise' => 'nullable|string',
            'ecole.telephone_ecole' => 'nullable|string',
            'ecole.embleme_ecole' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            'departements' => 'required|array|min:1',
            'departements.*.libelle_departement' => 'required|string',
            'departements.*.code_departement' => 'required|string|distinct',
            'departements.*.description_departement' => 'required|string',

            'departements.*.filieres' => 'required|array|min:1',
            'departements.*.filieres.*.libelle_filiere' => 'required|string',
            'departements.*.filieres.*.code_filiere' => 'required|string|distinct',
            'departements.*.filieres.*.description_filiere' => 'required|string',

            'departements.*.filieres.*.niveaux' => 'required|array|min:1',
            'departements.*.filieres.*.niveaux.*.niveau_id' => 'required|exists:niveaux,id',
            'departements.*.filieres.*.niveaux.*.matieres' => 'required|array|min:1',
            'departements.*.filieres.*.niveaux.*.matieres.*' => 'exists:matieres,id',
        ]);

        $ecole = DB::transaction(function () use ($request) {
            // Traitement du logo
            $logoPath = null;
            $bannierePath = null;
            if ($request->hasFile('ecole.logo')) {
                $logoFile = $request->file('ecole.logo');
                $logoFilename = 'logo_' . time() . '_' . $logoFile->getClientOriginalName();
                $logoPath = $logoFile->storeAs('logos_ecoles', $logoFilename, 'public');
            }

            if ($request->hasFile('ecole.banniere')) {
                $banniereFile = $request->file('ecole.banniere');
                $banniereFilename = 'banniere_' . time() . '_' . $banniereFile->getClientOriginalName();
                $bannierePath = $banniereFile->storeAs('bannieres_ecoles', $banniereFilename, 'public');
            }

            // Traitement de l'emblème (optionnel)
            $emblemePath = null;
            if ($request->hasFile('ecole.embleme_ecole')) {
                $emblemeFile = $request->file('ecole.embleme_ecole');
                $emblemeFilename = 'embleme_' . time() . '_' . $emblemeFile->getClientOriginalName();
                $emblemePath = $emblemeFile->storeAs('emblemes_ecoles', $emblemeFilename, 'public');
            }

            /** 1️⃣ ÉCOLE */
            $ecoleData = $request->ecole;
            $ecoleData['logo_url'] = $logoPath;
            $ecoleData['banniere_url'] = $bannierePath;
            if ($emblemePath) {
                $ecoleData['embleme_ecole'] = $emblemePath;
            }

            // Retirer les fichiers du tableau de données
            unset($ecoleData['logo'], $ecoleData['banniere'], $ecoleData['embleme_ecole']);

            $ecole = Ecole::create($ecoleData);

            /** 2️⃣ DÉPARTEMENTS */
            foreach ($request->departements as $depData) {

                $departement = Departement::create([
                    'libelle_departement' => $depData['libelle_departement'],
                    'code_departement' => $depData['code_departement'],
                    'description_departement' => $depData['description_departement'],
                    'ecole_id' => $ecole->id,
                ]);

                /** 3️⃣ FILIÈRES */
                foreach ($depData['filieres'] as $filData) {

                    $filiere = Filiere::create([
                        'libelle_filiere' => $filData['libelle_filiere'],
                        'code_filiere' => $filData['code_filiere'],
                        'description_filiere' => $filData['description_filiere'],
                        'departement_id' => $departement->id,
                    ]);

                    /** 4️⃣ NIVEAUX + MATIÈRES */
                    foreach ($filData['niveaux'] as $nivData) {

                        // Pivot filiere_niveaux
                        $filiere->niveaux()->attach($nivData['niveau_id']);

                        // Pivot niveau_matieres
                        DB::table('niveau_matieres')->insert(
                            collect($nivData['matieres'])->map(fn ($matiereId) => [
                                'niveau_id' => $nivData['niveau_id'],
                                'matiere_id' => $matiereId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ])->toArray()
                        );
                    }
                }
            }

            return $ecole;
        });

        return response()->json([
            'message' => 'École configurée avec succès',
            'data' => $ecole->load('departements.filieres.niveaux'),
            'logo_url' => $ecole->logo_url ? Storage::url($ecole->logo_url) : null,
            'embleme_url' => $ecole->embleme_ecole ? Storage::url($ecole->embleme_ecole) : null,
        ], 201);
    }

    /**
     * Mettre à jour l'école
     */
    public function update(Request $request, $id): JsonResponse
    {
        $ecole = Ecole::findOrFail($id);

        $request->validate([
            'libelle_ecole' => 'sometimes|string',
            'code_ecole' => 'sometimes|string|unique:ecoles,code_ecole,' . $id,
            'localisation' => 'sometimes|string',
            'email_ecole' => 'sometimes|email|unique:ecoles,email_ecole,' . $id,
            'logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banniere' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'bp_ecole' => 'nullable|string',
            'siteweb_ecole' => 'nullable|url',
            'devise' => 'nullable|string',
            'telephone_ecole' => 'nullable|string',
            'embleme_ecole' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::transaction(function () use ($request, $ecole) {
            // Mettre à jour le logo si fourni
            if ($request->hasFile('logo')) {
                // Supprimer l'ancien logo
                if ($ecole->logo_url && Storage::disk('public')->exists($ecole->logo_url)) {
                    Storage::disk('public')->delete($ecole->logo_url);
                }

                $logoFile = $request->file('logo');
                $logoFilename = 'logo_' . time() . '_' . $logoFile->getClientOriginalName();
                $logoPath = $logoFile->storeAs('logos_ecoles', $logoFilename, 'public');
                $ecole->logo_url = $logoPath;
            }

            if ($request->hasFile('banniere')) {
                if ($ecole->banniere_url && Storage::disk('public')->exists($ecole->banniere_url)) {
                    Storage::disk('public')->delete($ecole->banniere_url);
                }

                $banniereFile = $request->file('banniere');
                $banniereFilename = 'banniere_' . time() . '_' . $banniereFile->getClientOriginalName();
                $bannierePath = $banniereFile->storeAs('bannieres_ecoles', $banniereFilename, 'public');
                $ecole->banniere_url = $bannierePath;
            }

            // Mettre à jour l'emblème si fourni
            if ($request->hasFile('embleme_ecole')) {
                // Supprimer l'ancien emblème
                if ($ecole->embleme_ecole && Storage::disk('public')->exists($ecole->embleme_ecole)) {
                    Storage::disk('public')->delete($ecole->embleme_ecole);
                }

                $emblemeFile = $request->file('embleme_ecole');
                $emblemeFilename = 'embleme_' . time() . '_' . $emblemeFile->getClientOriginalName();
                $emblemePath = $emblemeFile->storeAs('emblemes_ecoles', $emblemeFilename, 'public');
                $ecole->embleme_ecole = $emblemePath;
            }

            // Mettre à jour les autres champs
            $ecole->fill($request->except(['logo', 'banniere', 'embleme_ecole']));
            $ecole->save();
        });

        return response()->json([
            'message' => 'École mise à jour avec succès',
            'data' => $ecole,
            'logo_url' => $ecole->logo_url ? Storage::url($ecole->logo_url) : null,
            'banniere_url' => $ecole->banniere_url ? Storage::url($ecole->banniere_url) : null,
            'embleme_url' => $ecole->embleme_ecole ? Storage::url($ecole->embleme_ecole) : null,
        ]);
    }

    /**
     * Mettre à jour uniquement le logo
     */
    public function updateLogo(Request $request, $id): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $ecole = Ecole::findOrFail($id);

        // Supprimer l'ancien logo
        if ($ecole->logo_url && Storage::disk('public')->exists($ecole->logo_url)) {
            Storage::disk('public')->delete($ecole->logo_url);
        }

        // Uploader le nouveau logo
        $logoFile = $request->file('logo');
        $logoFilename = 'logo_' . time() . '_' . $logoFile->getClientOriginalName();
        $logoPath = $logoFile->storeAs('logos_ecoles', $logoFilename, 'public');

        $ecole->logo_url = $logoPath;
        $ecole->save();

        return response()->json([
            'message' => 'Logo mis à jour avec succès',
            'logo_url' => Storage::url($logoPath),
            'data' => $ecole
        ]);
    }

    /**
     * Supprimer le logo
     */
    public function deleteLogo($id): JsonResponse
    {
        $ecole = Ecole::findOrFail($id);

        if (!$ecole->logo_url) {
            return response()->json(['message' => 'Aucun logo à supprimer'], 400);
        }

        // Supprimer le fichier
        if (Storage::disk('public')->exists($ecole->logo_url)) {
            Storage::disk('public')->delete($ecole->logo_url);
        }

        $ecole->logo_url = null;
        $ecole->save();

        return response()->json([
            'message' => 'Logo supprimé avec succès',
            'data' => $ecole
        ]);
    }
}
