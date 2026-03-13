<?php

namespace App\Http\Controllers\Api;

use App\Exports\CandidatsConcoursExport;
use App\Exports\ConcoursExport;
use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Concours;
use App\Models\FraisConcours;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ConcoursController extends Controller
{
    /**
     * Afficher la liste des concours
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 6);

            $query = Concours::with([
                'sessions' => function ($query) {
                    $query->orderBy('sessions.created_at', 'desc')->limit(1);
                },
                'concoursFilieres.filiere.departement.ecole',
                'fraisConcours'
            ]);

            // 🔍 Recherche
            if ($request->filled('search')) {
                $query->where('libelle_concours', 'like', '%' . $request->search . '%');
            }

            // 📅 Année
            if ($request->filled('annee')) {
                $query->whereHas('sessions', function ($q) use ($request) {
                    $q->where('annee', $request->annee);
                });
            }

            // 🔓 Statut
            if ($request->filled('statut')) {
                $query->whereHas('sessions', function ($q) use ($request) {
                    $q->where('statut', $request->statut);
                });
            }

            if ($request->filled('rubrique')) {
                $rubrique = $request->rubrique;
                $query->where('libelle_concours', 'LIKE', "%$rubrique%");
            }

            if ($request->boolean('raw')) {
                return response()->json($query->latest()->get());
            }

            // 🎠 MODE SLIDER (pas de pagination)
            if ($request->boolean('slider')) {
                return response()->json(
                    $query->latest()->take(10)->get()
                );
            }

            // 📄 MODE PAGINÉ (page catalogue)
            $concours = $query->latest()->paginate($perPage);

            return response()->json([
                'data' => $concours->items(),
                'meta' => [
                    'current_page' => $concours->currentPage(),
                    'per_page' => $concours->perPage(),
                    'total' => $concours->total(),
                    'last_page' => $concours->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération concours : ' . $e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de la récupération des concours'
            ], 500);
        }
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

            // Filieres
            'filieres' => 'required|array|min:1',
            'filieres.*.id' => 'required|exists:filieres,id',
            'filieres.*.nbre_places' => 'required|integer|min:1',

            // Session unique
            'session_id' => 'required|exists:sessions,id',

            // Informations de frais
            'frais_concours' => 'required|array',
            'frais_concours.montant_frais' => 'required|numeric|min:0',
            'frais_concours.nom_banque' => 'required|string|max:255',
            'frais_concours.numero_compte' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // 1️⃣ Créer le concours
            $concours = Concours::create($request->only([
                'libelle_concours',
                'date_limite',
                'date_examen'
            ]));

            // 2️⃣ Associer les filières avec nombre de places
            foreach ($request->filieres as $filiere) {
                $concours->filieres()->attach($filiere['id'], ['nbre_places' => $filiere['nbre_places']]);
            }

            // 3️⃣ Associer la session unique
            $concours->sessions()->attach($request->session_id);

            // 4️⃣ Créer les informations de frais de concours
            FraisConcours::create([
                'montant_frais' => $request->frais_concours['montant_frais'],
                'nom_banque' => $request->frais_concours['nom_banque'],
                'numero_compte' => $request->frais_concours['numero_compte'],
                'concours_id' => $concours->id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Concours créé avec succès',
                'concours' => $concours->load('filieres', 'sessions', 'fraisConcours')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la création du concours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un concours spécifique
     */
    /**
     * Afficher un concours spécifique
     */
    public function show($id)
    {
        $concours = Concours::with([
            'specification',
            'fraisConcours',
            'sessions' => function ($query) {
                $query
                    ->orderBy('sessions.created_at', 'desc')
                    ->limit(1);
            },
            'concoursFilieres' => function ($query) {
                $query->with([
                    'filiere.departement.ecole',
                    'filiere.niveaux'
                ]);
            }
        ])->find($id);

        if (!$concours) {
            return response()->json(['message' => 'Concours non trouvé'], 404);
        }

        // Vérifier si l'utilisateur est authentifié et est un candidat
        $isInscrit = false;
        $candidatureId = null;

        if (Auth::check()) {
            $user = Auth::user();

            // Vérifier si l'utilisateur a un profil candidat
            $candidat = $user->candidat;

            if ($candidat) {
                // Vérifier s'il existe une candidature pour ce concours
                $candidature = Candidature::where('candidat_id', $candidat->id)
                    ->where('concours_id', $id)
                    ->first();

                if ($candidature) {
                    $isInscrit = true;
                    $candidatureId = $candidature->id;
                }
            }
        }


        // Retourner les données du concours avec les infos d'inscription
        return response()->json([
            'concours' => $concours,
            'is_inscrit' => $isInscrit,
            'candidature_id' => $candidatureId,
        ]);
    }

    /**
     * Mettre à jour un concours
     */
    public function update(Request $request, $id)
    {
        $concours = Concours::with('fraisConcours')->find($id);

        if (!$concours) {
            return response()->json(['message' => 'Concours non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'libelle_concours' => 'sometimes|required|string|max:255',
            'date_limite' => 'sometimes|required|date',
            'date_examen' => 'sometimes|required|date',

            // Informations de frais (optionnelles pour la mise à jour)
            'frais_concours' => 'sometimes|array',
            'frais_concours.montant_frais' => 'sometimes|required_with:frais_concours|numeric|min:0',
            'frais_concours.nom_banque' => 'sometimes|required_with:frais_concours|string|max:255',
            'frais_concours.numero_compte' => 'sometimes|required_with:frais_concours|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            // 1️⃣ Mettre à jour le concours
            $concours->update($request->only([
                'libelle_concours',
                'date_limite',
                'date_examen'
            ]));

            // 2️⃣ Mettre à jour ou créer les informations de frais
            if ($request->has('frais_concours')) {
                if ($concours->fraisConcours) {
                    // Mettre à jour les frais existants
                    $concours->fraisConcours->update([
                        'montant_frais' => $request->frais_concours['montant_frais'] ?? $concours->fraisConcours->montant_frais,
                        'nom_banque' => $request->frais_concours['nom_banque'] ?? $concours->fraisConcours->nom_banque,
                        'numero_compte' => $request->frais_concours['numero_compte'] ?? $concours->fraisConcours->numero_compte,
                    ]);
                } else {
                    // Créer de nouveaux frais
                    FraisConcours::create([
                        'montant_frais' => $request->frais_concours['montant_frais'],
                        'nom_banque' => $request->frais_concours['nom_banque'],
                        'numero_compte' => $request->frais_concours['numero_compte'],
                        'concours_id' => $concours->id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Concours mis à jour avec succès',
                'concours' => $concours->load('fraisConcours')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du concours',
                'error' => $e->getMessage()
            ], 500);
        }
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

        DB::beginTransaction();

        try {
            // Supprimer les frais associés (cascade est configurée dans la migration)
            $concours->fraisConcours()->delete();

            // Supprimer le concours
            $concours->delete();

            DB::commit();

            return response()->json(['message' => 'Concours et ses frais associés supprimés avec succès']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de la suppression du concours',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les concours ouverts (date limite non dépassée)
     */
    public function getOpenConcours()
    {
        $concours = Concours::where('date_limite', '>=', now())
            ->with(['specification', 'fraisConcours'])
            ->latest()
            ->get();

        return response()->json($concours);
    }

    public function exportPdf(Request $request)
    {
        try {
            // Récupérer les concours avec les mêmes filtres que la méthode index
            $query = Concours::with([
                'sessions' => function ($query) {
                    $query->orderBy('sessions.created_at', 'desc')->limit(1);
                },
                'fraisConcours'
            ]);

            // Appliquer les filtres
            if ($request->filled('search')) {
                $query->where('libelle_concours', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('annee')) {
                $query->whereHas('sessions', function ($q) use ($request) {
                    $q->where('annee', $request->annee);
                });
            }

            if ($request->filled('statut')) {
                $query->whereHas('sessions', function ($q) use ($request) {
                    $q->where('statut', $request->statut);
                });
            }

            if ($request->filled('rubrique')) {
                $rubrique = $request->rubrique;
                $query->where('libelle_concours', 'LIKE', "%$rubrique%");
            }

            // Récupérer tous les concours filtrés
            $concours = $query->latest()->get();

            // Données pour le PDF
            $data = [
                'concours' => $concours,
                'filtres' => [
                    'search' => $request->search,
                    'annee' => $request->annee,
                    'statut' => $request->statut,
                    'date_export' => Carbon::now()->format('d/m/Y H:i'),
                    'total' => $concours->count(),
                ],
            ];

            // Générer le PDF
            $pdf = Pdf::loadView('exports.concours-pdf', $data)
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'Arial',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);

            // Retourner le PDF en téléchargement
            return $pdf->download('concours-' . Carbon::now()->format('Y-m-d-H-i') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Erreur export PDF concours : ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'export PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            // Récupérer les concours avec les mêmes filtres que la méthode index
            $query = Concours::with([
                'sessions' => function ($query) {
                    $query->orderBy('sessions.created_at', 'desc')->limit(1);
                },
                'fraisConcours'
            ]);

            // Appliquer les filtres
            if ($request->filled('search')) {
                $query->where('libelle_concours', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('annee')) {
                $query->whereHas('sessions', function ($q) use ($request) {
                    $q->where('annee', $request->annee);
                });
            }

            if ($request->filled('statut')) {
                $query->whereHas('sessions', function ($q) use ($request) {
                    $q->where('statut', $request->statut);
                });
            }

            if ($request->filled('rubrique')) {
                $rubrique = $request->rubrique;
                $query->where('libelle_concours', 'LIKE', "%$rubrique%");
            }

            // Récupérer tous les concours filtrés
            $concours = $query->latest()->get();

            // Transformer les données pour l'export Excel
            $exportData = $concours->map(function ($concour) {
                return [
                    'Concours' => $concour->libelle_concours,
                    'Session' => $concour->sessions->first()->libelle_session ?? 'N/A',
                    'Date limite' => $concour->date_limite ? Carbon::parse($concour->date_limite)->format('d/m/Y') : 'N/A',
                    'Date examen' => $concour->date_examen ? Carbon::parse($concour->date_examen)->format('d/m/Y') : 'N/A',
                    'Frais (FCFA)' => $concour->fraisConcours ? number_format($concour->fraisConcours->montant_frais, 0, ',', ' ') : '0',
                    'Banque' => $concour->fraisConcours->nom_banque ?? 'N/A',
                    'Compte' => $concour->fraisConcours->numero_compte ?? 'N/A',
                    'Nombre de filières' => $concour->concoursFilieres->count(),
                ];
            });

            // Créer l'export Excel
            $export = new ConcoursExport($exportData);

            // Télécharger le fichier Excel
            return Excel::download($export, 'concours-' . Carbon::now()->format('Y-m-d-H-i') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Erreur export Excel concours : ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'export Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getConcoursAvecCandidats()
    {
        try {
            $concours = Concours::has('candidatures')
                ->withCount('candidatures')
                ->with(['sessions' => function ($query) {
                    $query->orderBy('sessions.created_at', 'desc')->limit(1);
                }])
                ->orderBy('date_examen', 'desc')
                ->get()
                ->map(function ($concour) {
                    return [
                        'id' => $concour->id,
                        'libelle_concours' => $concour->libelle_concours,
                        'date_limite' => $concour->date_limite,
                        'date_examen' => $concour->date_examen,
                        'candidatures_count' => $concour->candidatures_count,
                        'session' => $concour->sessions->first()?->libelle_session,
                    ];
                });

            return response()->json($concours);
        } catch (\Exception $e) {
            Log::error('Erreur récupération concours avec candidats : ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la récupération des concours'
            ], 500);
        }
    }

    /**
     * Exporter la liste des candidats d'un concours en PDF
     */
    public function exportCandidatsPdf(Request $request, $concoursId)
    {
        try {
            // Vérifier que le concours existe
            $concours = Concours::with(['sessions', 'concoursFilieres.filiere.departement.ecole'])
                ->findOrFail($concoursId);

            // Récupérer les candidatures avec les informations des candidats
            $candidatures = Candidature::with([
                'candidat.utilisateur',
                'candidat',
                'centreExamen'
            ])
                ->where('concours_id', $concoursId)
                ->orderBy('created_at', 'asc')
                ->get();

            // Préparer les données pour le PDF
            $candidats = $candidatures->map(function ($candidature, $index) {
                return [
                    'numero' => $index + 1,
                    'nom' => $candidature->candidat?->utilisateur?->nom ?? 'N/A',
                    'prenom' => $candidature->candidat?->utilisateur?->prenom ?? 'N/A',
                    'email' => $candidature->candidat?->utilisateur?->email ?? 'N/A',
                    'sexe' => $candidature->candidat?->sexe === 'M' ? 'Masculin' : ($candidature->candidat?->sexe === 'F' ? 'Féminin' : 'N/A'),
                    'numero_cni' => $candidature->candidat?->numero_cni ?? 'N/A',
                    'statut' => $candidature->statut ?? 'N/A',
                    'code_candidat' => $candidature->code_candidat_def ?? $candidature->code_candidat_prov ?? 'N/A',
                    'centre_examen' => $candidature->centreExamen?->libelle_centre ?? 'Non affecté',
                ];
            });

            $data = [
                'concours' => $concours,
                'candidats' => $candidats,
                'total_candidats' => $candidats->count(),
                'date_export' => Carbon::now()->format('d/m/Y à H:i'),
                'ecole' => $concours->concoursFilieres->first()?->filiere?->departement?->ecole?->libelle_ecole ?? 'N/A',
                'session' => $concours->sessions->first()?->libelle_session ?? 'N/A',
            ];

            // Générer le PDF
            $pdf = Pdf::loadView('exports.candidats-concours-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont' => 'Arial',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                ]);

            $filename = 'candidats-' . \Illuminate\Support\Str::slug($concours->libelle_concours) . '-' . Carbon::now()->format('Y-m-d') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Erreur export PDF candidats : ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'export PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporter la liste des candidats d'un concours en Excel
     */
    public function exportCandidatsExcel(Request $request, $concoursId)
    {
        try {
            // Vérifier que le concours existe
            $concours = Concours::with(['sessions'])->findOrFail($concoursId);

            // Récupérer les candidatures avec les informations des candidats
            $candidatures = Candidature::with([
                'candidat.utilisateur',
                'candidat',
                'centreExamen'
            ])
                ->where('concours_id', $concoursId)
                ->orderBy('created_at', 'asc')
                ->get();

            // Créer l'export
            $export = new CandidatsConcoursExport($candidatures, $concours);

            $filename = 'candidats-' . \Illuminate\Support\Str::slug($concours->libelle_concours) . '-' . Carbon::now()->format('Y-m-d') . '.xlsx';

            return Excel::download($export, $filename);
        } catch (\Exception $e) {
            Log::error('Erreur export Excel candidats : ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de l\'export Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
