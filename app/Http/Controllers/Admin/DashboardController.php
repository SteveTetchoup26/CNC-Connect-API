<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Candidat;
use App\Models\Concours;
use App\Models\Ecole;
use App\Models\Utilisateur;
use App\Models\Centre;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Obtenir toutes les statistiques du dashboard
     */
    public function index()
    {
        return response()->json([
            'stats' => $this->getStats(),
            'candidatures_par_statut' => $this->getCandidaturesParStatut(),
            'candidatures_par_mois' => $this->getCandidaturesParMois(),
            'candidatures_par_ecole' => $this->getCandidaturesParEcole(),
            'candidatures_par_concours' => $this->getCandidaturesParConcours(),
            'concours_actifs' => $this->getConcoursActifs(),
            'candidatures_recentes' => $this->getCandidaturesRecentes(),
            'taux_reussite' => $this->getTauxReussite(),
        ]);
    }

    /* ======================================================
       🔁 SOUS-REQUÊTE : DERNIER ÉTAT PAR CANDIDATURE
    ====================================================== */
    private function lastEtatCandidatureSubquery()
    {
        return DB::table('historique_candidatures as h')
            ->join(
                DB::raw('(
                    SELECT candidature_id, MAX(date_etat) as last_date
                    FROM historique_candidatures
                    GROUP BY candidature_id
                ) as last_hist'),
                function ($join) {
                    $join->on('h.candidature_id', '=', 'last_hist.candidature_id')
                        ->on('h.date_etat', '=', 'last_hist.last_date');
                }
            )
            ->join('etat_candidatures as e', 'h.etat_id', '=', 'e.id');
    }

    /* ======================================================
       📊 STATISTIQUES GÉNÉRALES
    ====================================================== */
    private function getStats()
    {
        $lastEtat = $this->lastEtatCandidatureSubquery();

        return [
            'total_candidatures' => Candidature::count(),

            'candidatures_en_attente' => (clone $lastEtat)
                ->where('e.libelle_etat', 'EN_ATTENTE')
                ->count(),

            'candidatures_validees' => (clone $lastEtat)
                ->where('e.libelle_etat', 'VALIDEE')
                ->count(),

            'candidatures_rejetees' => (clone $lastEtat)
                ->where('e.libelle_etat', 'REJETEE')
                ->count(),

            'candidatures_approuvees' => (clone $lastEtat)
                ->where('e.libelle_etat', 'APPROUVEE')
                ->count(),

            'candidatures_ce_mois' => Candidature::whereMonth('date_candidature', now()->month)
                ->whereYear('date_candidature', now()->year)
                ->count(),

            'candidatures_aujourdhui' => Candidature::whereDate('date_candidature', today())
                ->count(),

            'total_utilisateurs' => Utilisateur::count(),
            'total_candidats' => Candidat::count(),

            'total_concours' => Concours::count(),
            'concours_actifs' => Concours::where('date_limite', '>=', now())->count(),
            'concours_termines' => Concours::where('date_examen', '<', now())->count(),

            'total_ecoles' => Ecole::count(),
            'total_centres' => Centre::count(),
            'centres_depot' => Centre::where('type_centre', 'CENTRE_DE_DEPÔT')->count(),
            'centres_examen' => Centre::where('type_centre', 'CENTRE_EXAMEN')->count(),

            'total_sessions' => Session::count(),
        ];
    }

    /* ======================================================
       🟠 CANDIDATURES PAR STATUT (DONUT)
    ====================================================== */
    private function getCandidaturesParStatut()
    {
        return $this->lastEtatCandidatureSubquery()
            ->select(
                'e.libelle_etat as statut',
                'e.libelle_etat as label',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('e.libelle_etat', 'e.libelle_etat')
            ->get();
    }

    /* ======================================================
       📈 CANDIDATURES PAR MOIS
    ====================================================== */
    private function getCandidaturesParMois()
    {
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);

            $data[] = [
                'mois' => $date->format('M Y'),
                'mois_complet' => $date->locale('fr')->isoFormat('MMMM YYYY'),
                'total' => Candidature::whereYear('date_candidature', $date->year)
                    ->whereMonth('date_candidature', $date->month)
                    ->count(),
                'annee' => $date->year,
                'mois_numero' => $date->month,
            ];
        }

        return $data;
    }

    /* ======================================================
       🏫 CANDIDATURES PAR ÉCOLE
    ====================================================== */
    private function getCandidaturesParEcole()
    {
        return Candidature::select('ecoles.libelle_ecole', DB::raw('COUNT(candidatures.id) as total'))
            ->join('concours', 'candidatures.concours_id', '=', 'concours.id')
            ->join('concours_filieres', 'concours.id', '=', 'concours_filieres.concours_id')
            ->join('filieres', 'concours_filieres.filiere_id', '=', 'filieres.id')
            ->join('departements', 'filieres.departement_id', '=', 'departements.id')
            ->join('ecoles', 'departements.ecole_id', '=', 'ecoles.id')
            ->groupBy('ecoles.id', 'ecoles.libelle_ecole')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    /* ======================================================
       🏆 CANDIDATURES PAR CONCOURS
    ====================================================== */
    private function getCandidaturesParConcours()
    {
        return Candidature::select('concours.libelle_concours', DB::raw('COUNT(*) as total'))
            ->join('concours', 'candidatures.concours_id', '=', 'concours.id')
            ->groupBy('concours.id', 'concours.libelle_concours')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    /* ======================================================
       ⏳ CONCOURS ACTIFS
    ====================================================== */
   private function getConcoursActifs()
{
    return Concours::with(['concoursFilieres.filiere.departement.ecole', 'fraisConcours'])
        ->where('date_limite', '>=', now())
        ->orderBy('date_limite')
        ->limit(5)
        ->get()
        ->map(function ($c) {

            // secondes restantes (date limite - maintenant)
            $secondsRestants = now()->diffInSeconds(
                Carbon::parse($c->date_limite)->startOfDay(),
                false
            );

            // jours décimaux
            $joursDecimaux = $secondsRestants / 86400;

            // arrondi mathématique classique
            $joursRestants = round($joursDecimaux, 0, PHP_ROUND_HALF_UP);

            return [
                'id' => $c->id,
                'libelle_concours' => $c->libelle_concours,
                'date_limite' => $c->date_limite->toDateString(),
                'date_examen' => $c->date_examen?->toDateString(),
                'ecole' => $c->concoursFilieres->first()?->filiere?->departement?->ecole?->libelle_ecole,
                'montant_frais' => $c->fraisConcours?->montant_frais,
                'jours_restants' => max(0, (int) $joursRestants),
            ];
        });
}



    /* ======================================================
       🆕 CANDIDATURES RÉCENTES
    ====================================================== */
    private function getCandidaturesRecentes()
    {
        $lastEtat = $this->lastEtatCandidatureSubquery();

        return Candidature::with(['candidat.utilisateur', 'concours.concoursFilieres.filiere.departement.ecole'])
            ->orderBy('date_candidature', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($c) use ($lastEtat) {
                $etat = (clone $lastEtat)->where('h.candidature_id', $c->id)->first();

                return [
                    'id' => $c->id,
                    'candidat' => $c->candidat?->utilisateur?->nom . ' ' . $c->candidat?->utilisateur?->prenom,
                    'email' => $c->candidat?->utilisateur?->email,
                    'concours' => $c->concours?->libelle_concours,
                    'ecole' => $c->concours?->concoursFilieres->first()?->filiere?->departement?->ecole?->libelle_ecole,
                    'date_candidature' => $c->date_candidature,
                    'statut' => $etat?->libelle_etat,
                ];
            });
    }

    /* ======================================================
       📊 TAUX DE RÉUSSITE
    ====================================================== */
    private function getTauxReussite()
    {
        $lastEtat = $this->lastEtatCandidatureSubquery();

        $total = Candidature::count();
        $approuvees = (clone $lastEtat)->where('e.libelle_etat', 'APPROUVEE')->count();
        $rejetees = (clone $lastEtat)->where('e.libelle_etat', 'REJETEE')->count();
        $enAttente = (clone $lastEtat)->where('e.libelle_etat', 'EN_ATTENTE')->count();

        return [
            'total' => $total,
            'approuvees' => $approuvees,
            'rejetees' => $rejetees,
            'en_attente' => $enAttente,
            'taux_approbation' => $total ? round(($approuvees / $total) * 100, 2) : 0,
            'taux_rejet' => $total ? round(($rejetees / $total) * 100, 2) : 0,
        ];
    }
}
