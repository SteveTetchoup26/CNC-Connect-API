<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ecole;
use App\Models\Departement;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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

            /** 1️⃣ ÉCOLE */
            $ecole = Ecole::create($request->ecole);

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
            'data' => $ecole->load('departements.filieres.niveaux')
        ], 201);
    }
}
