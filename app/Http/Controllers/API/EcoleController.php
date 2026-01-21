<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ecole;
use App\Models\Departement;
use App\Models\Filiere;
use App\Models\Matiere;
use App\Models\Niveau;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EcoleController extends Controller
{
    /**
     * Création complète via Stepper
     */
    public function index(): JsonResponse
    {
        return response()->json(
            Ecole::with('departements.filieres.niveaux.matieres')->get()
        );
    }

    /**
     * 🔍 Détails complets (EDIT / VIEW)
     */
    public function show(int $id): JsonResponse
    {
        $ecole = Ecole::with([
            'departements.filieres.niveaux.matieres'
        ])->findOrFail($id);

        return response()->json([
            'data' => $ecole,
            'logo_url' => $ecole->logo_url ? Storage::url($ecole->logo_url) : null,
            'banniere_url' => $ecole->banniere_url ? Storage::url($ecole->banniere_url) : null,
            'embleme_url' => $ecole->embleme_ecole ? Storage::url($ecole->embleme_ecole) : null,
        ]);
    }

    /**
     * ➕ Création complète via Stepper
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ecole.libelle_ecole' => 'required|string',
            'ecole.code_ecole' => 'required|string|unique:ecoles,code_ecole',
            'ecole.localisation' => 'required|string',
            'ecole.email_ecole' => 'required|email|unique:ecoles,email_ecole',
            'ecole.bp_ecole' => 'nullable|string',
            'ecole.siteweb_ecole' => 'nullable|string',
            'ecole.telephone_ecole' => 'required|string',
            'ecole.logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'ecole.banniere' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'ecole.embleme_ecole' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            'departements' => 'required|array|min:1',
            'departements.*.libelle_departement' => 'required|string',
            'departements.*.code_departement' => 'required|string',
            'departements.*.description_departement' => 'nullable|string',

            'departements.*.filieres' => 'required|array|min:1',
            'departements.*.filieres.*.libelle_filiere' => 'required|string',
            'departements.*.filieres.*.code_filiere' => 'required|string',
            'departements.*.filieres.*.description_filiere' => 'nullable|string',

            'departements.*.filieres.*.niveaux' => 'required|array|min:1',
            'departements.*.filieres.*.niveaux.*.libelle_niveau' => 'required|string',
            'departements.*.filieres.*.niveaux.*.code_niveau' => 'nullable|string',

            'departements.*.filieres.*.niveaux.*.matieres' => 'required|array|min:1',
            'departements.*.filieres.*.niveaux.*.matieres.*.libelle_matiere' => 'required|string',
            'departements.*.filieres.*.niveaux.*.matieres.*.code_matiere' => 'required|string',
        ]);

        $ecole = DB::transaction(function () use ($request) {

            /* 📁 FICHIERS */
            $logoPath = $request->file('ecole.logo')
                ? $request->file('ecole.logo')->store('logos_ecoles', 'public')
                : null;

            $bannierePath = $request->file('ecole.banniere')
                ? $request->file('ecole.banniere')->store('bannieres_ecoles', 'public')
                : null;

            $emblemePath = $request->file('ecole.embleme_ecole')
                ? $request->file('ecole.embleme_ecole')->store('emblemes_ecoles', 'public')
                : null;

            /* 🏫 ÉCOLE */
            $ecoleData = $request->ecole;
            $ecoleData['logo_url'] = $logoPath;
            $ecoleData['banniere_url'] = $bannierePath;
            $ecoleData['embleme_ecole'] = $emblemePath;

            unset($ecoleData['logo'], $ecoleData['banniere'], $ecoleData['embleme_ecole']);

            $ecole = Ecole::create($ecoleData);

            /* 🧱 STRUCTURE */
            foreach ($request->departements as $depData) {

                $departement = Departement::create([
                    'libelle_departement' => $depData['libelle_departement'],
                    'code_departement' => $depData['code_departement'],
                    'description_departement' => $depData['description_departement'],
                    'ecole_id' => $ecole->id,
                ]);

                foreach ($depData['filieres'] as $filData) {

                    $filiere = Filiere::create([
                        'libelle_filiere' => $filData['libelle_filiere'],
                        'code_filiere' => $filData['code_filiere'],
                        'description_filiere' => $filData['description_filiere'],
                        'departement_id' => $departement->id,
                    ]);

                    foreach ($filData['niveaux'] as $nivData) {

                        $niveau = Niveau::firstOrCreate(
                            // ['code_niveau' => $nivData['code_niveau']],
                            ['libelle_niveau' => $nivData['libelle_niveau']]
                        );

                        $filiere->niveaux()->syncWithoutDetaching($niveau->id);

                        foreach ($nivData['matieres'] as $matData) {

                            $matiere = Matiere::firstOrCreate(
                                ['code_matiere' => $matData['code_matiere']],
                                ['libelle_matiere' => $matData['libelle_matiere']]
                            );

                            $niveau->matieres()->syncWithoutDetaching($matiere->id);
                        }
                    }
                }
            }

            return $ecole;
        });

        return response()->json([
            'message' => 'École créée avec succès',
            'data' => $ecole->load('departements.filieres.niveaux.matieres')
        ], 201);
    }

    /**
     * ✏️ Mise à jour complète (Stepper EDIT)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $ecole = Ecole::findOrFail($id);

        DB::transaction(function () use ($request, $ecole) {

            /* 🔄 ÉCOLE */
            $ecole->update($request->except([
                'logo',
                'banniere',
                'embleme_ecole',
                'departements'
            ]));

            /* 🔁 RESET STRUCTURE */
            foreach ($ecole->departements as $dep) {
                foreach ($dep->filieres as $fil) {
                    $fil->niveaux()->detach();
                }
            }

            $ecole->departements()->delete();

            /* 🔁 REBUILD STRUCTURE */
            foreach ($request->departements as $depData) {

                $departement = Departement::create([
                    'libelle_departement' => $depData['libelle_departement'],
                    'code_departement' => $depData['code_departement'],
                    'description_departement' => $depData['description_departement'],
                    'ecole_id' => $ecole->id,
                ]);

                foreach ($depData['filieres'] as $filData) {

                    $filiere = Filiere::create([
                        'libelle_filiere' => $filData['libelle_filiere'],
                        'code_filiere' => $filData['code_filiere'],
                        'description_filiere' => $filData['description_filiere'],
                        'departement_id' => $departement->id,
                    ]);

                    foreach ($filData['niveaux'] as $nivData) {

                        $niveau = Niveau::firstOrCreate(
                            ['code_niveau' => $nivData['code_niveau']],
                            ['libelle_niveau' => $nivData['libelle_niveau']]
                        );

                        $filiere->niveaux()->syncWithoutDetaching($niveau->id);

                        foreach ($nivData['matieres'] as $matData) {

                            $matiere = Matiere::firstOrCreate(
                                ['code_matiere' => $matData['code_matiere']],
                                ['libelle_matiere' => $matData['libelle_matiere']]
                            );

                            $niveau->matieres()->syncWithoutDetaching($matiere->id);
                        }
                    }
                }
            }
        });

        return response()->json([
            'message' => 'École mise à jour avec succès',
            'data' => $ecole->load('departements.filieres.niveaux.matieres')
        ]);
    }

    /**
     * 🗑 Suppression complète
     */
    public function destroy(int $id): JsonResponse
    {
        $ecole = Ecole::findOrFail($id);

        DB::transaction(function () use ($ecole) {

            foreach (['logo_url', 'banniere_url', 'embleme_ecole'] as $file) {
                if ($ecole->$file && Storage::disk('public')->exists($ecole->$file)) {
                    Storage::disk('public')->delete($ecole->$file);
                }
            }

            $ecole->departements()->delete();
            $ecole->delete();
        });

        return response()->json(['message' => 'École supprimée']);
    }

    /**
     * 📦 Données pour Stepper
     */
    public function formData(): JsonResponse
    {
        return response()->json([
            'niveaux' => Niveau::all(),
            'matieres' => Matiere::all(),
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
