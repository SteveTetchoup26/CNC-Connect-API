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
    public function index(Request $request)
    {
        Log::info('Récupération de la liste des concours', [
            'filters' => $request->only(['search', 'annee', 'statut', 'rubrique', 'per_page']),
            'user_id' => Auth::id()
        ]);

        try {
            $perPage = $request->get('per_page', 6);

            $query = Concours::with([
                'sessions' => function ($query) {
                    $query->orderBy('sessions.created_at', 'desc')->limit(1);
                },
                'concoursFilieres.filiere.departement.ecole',
                'fraisConcours'
            ]);

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

            if ($request->boolean('raw')) {
                $result = $query->latest()->get();
                Log::info('Liste concours récupérée (raw)', ['count' => $result->count()]);
                return response()->json($result);
            }

            if ($request->boolean('slider')) {
                $result = $query->latest()->take(10)->get();
                Log::info('Liste concours récupérée (slider)', ['count' => $result->count()]);
                return response()->json($result);
            }

            $concours = $query->latest()->paginate($perPage);

            Log::info('Liste concours paginée récupérée', [
                'total' => $concours->total(),
                'page' => $concours->currentPage()
            ]);

            return response()->json([
                'data' => $concours->items(),
                'meta' => [
                    'current_page' => $concours->currentPage(),
                    'per_page'     => $concours->perPage(),
                    'total'        => $concours->total(),
                    'last_page'    => $concours->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération concours', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Erreur lors de la récupération des concours'], 500);
        }
    }

    public function store(Request $request)
    {
        Log::info('Tentative de création d\'un concours', [
            'data'    => $request->except(['frais_concours']),
            'user_id' => Auth::id()
        ]);

        $validator = Validator::make($request->all(), [
            'libelle_concours'                  => 'required|string|max:255',
            'date_limite'                       => 'required|date',
            'date_examen'                       => 'required|date|after:date_limite',
            'filieres'                          => 'required|array|min:1',
            'filieres.*.id'                     => 'required|exists:filieres,id',
            'filieres.*.nbre_places'            => 'required|integer|min:1',
            'session_id'                        => 'required|exists:sessions,id',
            'frais_concours'                    => 'required|array',
            'frais_concours.montant_frais'      => 'required|numeric|min:0',
            'frais_concours.nom_banque'         => 'required|string|max:255',
            'frais_concours.numero_compte'      => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation échouée lors de la création d\'un concours', [
                'errors'  => $validator->errors()->toArray(),
                'user_id' => Auth::id()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $concours = Concours::create($request->only([
                'libelle_concours', 'date_limite', 'date_examen'
            ]));

            foreach ($request->filieres as $filiere) {
                $concours->filieres()->attach($filiere['id'], ['nbre_places' => $filiere['nbre_places']]);
            }

            $concours->sessions()->attach($request->session_id);

            FraisConcours::create([
                'montant_frais'  => $request->frais_concours['montant_frais'],
                'nom_banque'     => $request->frais_concours['nom_banque'],
                'numero_compte'  => $request->frais_concours['numero_compte'],
                'concours_id'    => $concours->id,
            ]);

            DB::commit();

            Log::info('Concours créé avec succès', [
                'concours_id'      => $concours->id,
                'libelle_concours' => $concours->libelle_concours,
                'session_id'       => $request->session_id,
                'nb_filieres'      => count($request->filieres),
                'user_id'          => Auth::id()
            ]);

            return response()->json([
                'message'  => 'Concours créé avec succès',
                'concours' => $concours->load('filieres', 'sessions', 'fraisConcours')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du concours', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'message' => 'Erreur lors de la création du concours',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        Log::info('Consultation d\'un concours', [
            'concours_id' => $id,
            'user_id'     => Auth::id()
        ]);

        $concours = Concours::with([
            'specification',
            'fraisConcours',
            'sessions' => function ($query) {
                $query->orderBy('sessions.created_at', 'desc')->limit(1);
            },
            'concoursFilieres' => function ($query) {
                $query->with(['filiere.departement.ecole', 'filiere.niveaux']);
            }
        ])->find($id);

        if (!$concours) {
            Log::warning('Concours non trouvé', ['concours_id' => $id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Concours non trouvé'], 404);
        }

        $isInscrit    = false;
        $candidatureId = null;

        if (Auth::check()) {
            $user     = Auth::user();
            $candidat = $user->candidat;

            if ($candidat) {
                $candidature = Candidature::where('candidat_id', $candidat->id)
                    ->where('concours_id', $id)
                    ->first();

                if ($candidature) {
                    $isInscrit     = true;
                    $candidatureId = $candidature->id;
                    Log::info('Candidat déjà inscrit au concours', [
                        'concours_id'    => $id,
                        'candidat_id'    => $candidat->id,
                        'candidature_id' => $candidature->id
                    ]);
                } else {
                    Log::info('Candidat non inscrit au concours', [
                        'concours_id' => $id,
                        'candidat_id' => $candidat->id
                    ]);
                }
            }
        }

        Log::info('Concours consulté avec succès', [
            'concours_id'      => $id,
            'libelle_concours' => $concours->libelle_concours,
            'is_inscrit'       => $isInscrit
        ]);

        return response()->json([
            'concours'       => $concours,
            'is_inscrit'     => $isInscrit,
            'candidature_id' => $candidatureId,
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info('Tentative de mise à jour d\'un concours', [
            'concours_id' => $id,
            'data'        => $request->except(['frais_concours']),
            'user_id'     => Auth::id()
        ]);

        $concours = Concours::with('fraisConcours')->find($id);

        if (!$concours) {
            Log::warning('Concours non trouvé pour mise à jour', ['concours_id' => $id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Concours non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'libelle_concours'                          => 'sometimes|required|string|max:255',
            'date_limite'                               => 'sometimes|required|date',
            'date_examen'                               => 'sometimes|required|date',
            'frais_concours'                            => 'sometimes|array',
            'frais_concours.montant_frais'              => 'sometimes|required_with:frais_concours|numeric|min:0',
            'frais_concours.nom_banque'                 => 'sometimes|required_with:frais_concours|string|max:255',
            'frais_concours.numero_compte'              => 'sometimes|required_with:frais_concours|string|max:50',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation échouée lors de la mise à jour d\'un concours', [
                'concours_id' => $id,
                'errors'      => $validator->errors()->toArray(),
                'user_id'     => Auth::id()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $concours->update($request->only(['libelle_concours', 'date_limite', 'date_examen']));

            if ($request->has('frais_concours')) {
                if ($concours->fraisConcours) {
                    $concours->fraisConcours->update([
                        'montant_frais' => $request->frais_concours['montant_frais'] ?? $concours->fraisConcours->montant_frais,
                        'nom_banque'    => $request->frais_concours['nom_banque']    ?? $concours->fraisConcours->nom_banque,
                        'numero_compte' => $request->frais_concours['numero_compte'] ?? $concours->fraisConcours->numero_compte,
                    ]);
                    Log::info('Frais du concours mis à jour', ['concours_id' => $id]);
                } else {
                    FraisConcours::create([
                        'montant_frais' => $request->frais_concours['montant_frais'],
                        'nom_banque'    => $request->frais_concours['nom_banque'],
                        'numero_compte' => $request->frais_concours['numero_compte'],
                        'concours_id'   => $concours->id,
                    ]);
                    Log::info('Frais du concours créés lors de la mise à jour', ['concours_id' => $id]);
                }
            }

            DB::commit();

            Log::info('Concours mis à jour avec succès', [
                'concours_id'      => $id,
                'libelle_concours' => $concours->libelle_concours,
                'user_id'          => Auth::id()
            ]);

            return response()->json([
                'message'  => 'Concours mis à jour avec succès',
                'concours' => $concours->load('fraisConcours')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du concours', [
                'concours_id' => $id,
                'message'     => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
                'user_id'     => Auth::id()
            ]);
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du concours',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::info('Tentative de suppression d\'un concours', [
            'concours_id' => $id,
            'user_id'     => Auth::id()
        ]);

        $concours = Concours::find($id);

        if (!$concours) {
            Log::warning('Concours non trouvé pour suppression', ['concours_id' => $id, 'user_id' => Auth::id()]);
            return response()->json(['message' => 'Concours non trouvé'], 404);
        }

        DB::beginTransaction();

        try {
            $libelle = $concours->libelle_concours;
            $concours->fraisConcours()->delete();
            $concours->delete();

            DB::commit();

            Log::info('Concours supprimé avec succès', [
                'concours_id'      => $id,
                'libelle_concours' => $libelle,
                'user_id'          => Auth::id()
            ]);

            return response()->json(['message' => 'Concours et ses frais associés supprimés avec succès']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du concours', [
                'concours_id' => $id,
                'message'     => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
                'user_id'     => Auth::id()
            ]);
            return response()->json([
                'message' => 'Erreur lors de la suppression du concours',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getOpenConcours()
    {
        Log::info('Récupération des concours ouverts', ['user_id' => Auth::id()]);

        $concours = Concours::where('date_limite', '>=', now())
            ->with(['specification', 'fraisConcours'])
            ->latest()
            ->get();

        Log::info('Concours ouverts récupérés', ['count' => $concours->count()]);

        return response()->json($concours);
    }

    public function exportPdf(Request $request)
    {
        Log::info('Export PDF concours demandé', [
            'filters' => $request->only(['search', 'annee', 'statut', 'rubrique']),
            'user_id' => Auth::id()
        ]);

        try {
            $query = Concours::with([
                'sessions' => function ($query) {
                    $query->orderBy('sessions.created_at', 'desc')->limit(1);
                },
                'fraisConcours'
            ]);

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
                $query->where('libelle_concours', 'LIKE', "%{$request->rubrique}%");
            }

            $concours = $query->latest()->get();

            Log::info('Données PDF concours préparées', ['total' => $concours->count(), 'user_id' => Auth::id()]);

            $data = [
                'concours' => $concours,
                'filtres'  => [
                    'search'      => $request->search,
                    'annee'       => $request->annee,
                    'statut'      => $request->statut,
                    'date_export' => Carbon::now()->format('d/m/Y H:i'),
                    'total'       => $concours->count(),
                ],
            ];

            $pdf = Pdf::loadView('exports.concours-pdf', $data)
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'defaultFont'          => 'Arial',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => true,
                ]);

            Log::info('Export PDF concours généré avec succès', ['user_id' => Auth::id()]);

            return $pdf->download('concours-' . Carbon::now()->format('Y-m-d-H-i') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur export PDF concours', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'message' => 'Erreur lors de l\'export PDF',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        Log::info('Export Excel concours demandé', [
            'filters' => $request->only(['search', 'annee', 'statut', 'rubrique']),
            'user_id' => Auth::id()
        ]);

        try {
            $query = Concours::with([
                'sessions' => function ($query) {
                    $query->orderBy('sessions.created_at', 'desc')->limit(1);
                },
                'fraisConcours'
            ]);

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
                $query->where('libelle_concours', 'LIKE', "%{$request->rubrique}%");
            }

            $concours = $query->latest()->get();

            Log::info('Données Excel concours préparées', ['total' => $concours->count(), 'user_id' => Auth::id()]);

            $exportData = $concours->map(function ($concour) {
                return [
                    'Concours'          => $concour->libelle_concours,
                    'Session'           => $concour->sessions->first()->libelle_session ?? 'N/A',
                    'Date limite'       => $concour->date_limite  ? Carbon::parse($concour->date_limite)->format('d/m/Y')  : 'N/A',
                    'Date examen'       => $concour->date_examen  ? Carbon::parse($concour->date_examen)->format('d/m/Y')  : 'N/A',
                    'Frais (FCFA)'      => $concour->fraisConcours ? number_format($concour->fraisConcours->montant_frais, 0, ',', ' ') : '0',
                    'Banque'            => $concour->fraisConcours->nom_banque    ?? 'N/A',
                    'Compte'            => $concour->fraisConcours->numero_compte ?? 'N/A',
                    'Nombre de filières'=> $concour->concoursFilieres->count(),
                ];
            });

            $export = new ConcoursExport($exportData);

            Log::info('Export Excel concours généré avec succès', ['user_id' => Auth::id()]);

            return Excel::download($export, 'concours-' . Carbon::now()->format('Y-m-d-H-i') . '.xlsx');

        } catch (\Exception $e) {
            Log::error('Erreur export Excel concours', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'message' => 'Erreur lors de l\'export Excel',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function getConcoursAvecCandidats()
    {
        Log::info('Récupération des concours avec candidats', ['user_id' => Auth::id()]);

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
                        'id'                 => $concour->id,
                        'libelle_concours'   => $concour->libelle_concours,
                        'date_limite'        => $concour->date_limite,
                        'date_examen'        => $concour->date_examen,
                        'candidatures_count' => $concour->candidatures_count,
                        'session'            => $concour->sessions->first()?->libelle_session,
                    ];
                });

            Log::info('Concours avec candidats récupérés', ['count' => $concours->count(), 'user_id' => Auth::id()]);

            return response()->json($concours);

        } catch (\Exception $e) {
            Log::error('Erreur récupération concours avec candidats', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json(['message' => 'Erreur lors de la récupération des concours'], 500);
        }
    }

    public function exportCandidatsPdf(Request $request, $concoursId)
    {
        Log::info('Export PDF candidats d\'un concours demandé', [
            'concours_id' => $concoursId,
            'user_id'     => Auth::id()
        ]);

        try {
            $concours = Concours::with(['sessions', 'concoursFilieres.filiere.departement.ecole'])
                ->findOrFail($concoursId);

            $candidatures = Candidature::with(['candidat.utilisateur', 'candidat', 'centreExamen'])
                ->where('concours_id', $concoursId)
                ->orderBy('created_at', 'asc')
                ->get();

            Log::info('Candidats trouvés pour export PDF', [
                'concours_id' => $concoursId,
                'total'       => $candidatures->count()
            ]);

            $candidats = $candidatures->map(function ($candidature, $index) {
                return [
                    'numero'        => $index + 1,
                    'nom'           => $candidature->candidat?->utilisateur?->nom    ?? 'N/A',
                    'prenom'        => $candidature->candidat?->utilisateur?->prenom ?? 'N/A',
                    'email'         => $candidature->candidat?->utilisateur?->email  ?? 'N/A',
                    'sexe'          => $candidature->candidat?->sexe === 'M' ? 'Masculin' : ($candidature->candidat?->sexe === 'F' ? 'Féminin' : 'N/A'),
                    'numero_cni'    => $candidature->candidat?->numero_cni ?? 'N/A',
                    'statut'        => $candidature->statut ?? 'N/A',
                    'code_candidat' => $candidature->code_candidat_def ?? $candidature->code_candidat_prov ?? 'N/A',
                    'centre_examen' => $candidature->centreExamen?->libelle_centre ?? 'Non affecté',
                ];
            });

            $data = [
                'concours'       => $concours,
                'candidats'      => $candidats,
                'total_candidats'=> $candidats->count(),
                'date_export'    => Carbon::now()->format('d/m/Y à H:i'),
                'ecole'          => $concours->concoursFilieres->first()?->filiere?->departement?->ecole?->libelle_ecole ?? 'N/A',
                'session'        => $concours->sessions->first()?->libelle_session ?? 'N/A',
            ];

            $pdf = Pdf::loadView('exports.candidats-concours-pdf', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'defaultFont'          => 'Arial',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled'      => true,
                ]);

            $filename = 'candidats-' . \Illuminate\Support\Str::slug($concours->libelle_concours) . '-' . Carbon::now()->format('Y-m-d') . '.pdf';

            Log::info('Export PDF candidats généré avec succès', [
                'concours_id' => $concoursId,
                'filename'    => $filename,
                'user_id'     => Auth::id()
            ]);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Erreur export PDF candidats', [
                'concours_id' => $concoursId,
                'message'     => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
                'user_id'     => Auth::id()
            ]);
            return response()->json([
                'message' => 'Erreur lors de l\'export PDF',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function exportCandidatsExcel(Request $request, $concoursId)
    {
        Log::info('Export Excel candidats d\'un concours demandé', [
            'concours_id' => $concoursId,
            'user_id'     => Auth::id()
        ]);

        try {
            $concours = Concours::with(['sessions'])->findOrFail($concoursId);

            $candidatures = Candidature::with(['candidat.utilisateur', 'candidat', 'centreExamen'])
                ->where('concours_id', $concoursId)
                ->orderBy('created_at', 'asc')
                ->get();

            Log::info('Candidats trouvés pour export Excel', [
                'concours_id' => $concoursId,
                'total'       => $candidatures->count()
            ]);

            $export   = new CandidatsConcoursExport($candidatures, $concours);
            $filename = 'candidats-' . \Illuminate\Support\Str::slug($concours->libelle_concours) . '-' . Carbon::now()->format('Y-m-d') . '.xlsx';

            Log::info('Export Excel candidats généré avec succès', [
                'concours_id' => $concoursId,
                'filename'    => $filename,
                'user_id'     => Auth::id()
            ]);

            return Excel::download($export, $filename);

        } catch (\Exception $e) {
            Log::error('Erreur export Excel candidats', [
                'concours_id' => $concoursId,
                'message'     => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
                'user_id'     => Auth::id()
            ]);
            return response()->json([
                'message' => 'Erreur lors de l\'export Excel',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
