<?php

use App\Http\Controllers\Admin\CandidatureController;
use App\Http\Controllers\Admin\CentreController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EpreuveController as AdminEpreuveController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\api\CandidatController;
use App\Http\Controllers\Api\ConcoursController;
use App\Http\Controllers\Api\EcoleController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SpecificationConcoursController;
use App\Http\Controllers\Admin\UtilisateurController;
use App\Models\Epreuve;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/concours', [ConcoursController::class, 'index']);
    Route::get('/concours/{id}', [ConcoursController::class, 'show']) ->middleware('optional.sanctum');;


    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

        Route::get('/spec-concours', [SpecificationConcoursController::class, 'show']);
        Route::get('/spec-concours/concours/{concours_id}', [SpecificationConcoursController::class, 'showByConcours']);
        Route::get('/spec-concours/concours/{concours_id}/documents', [SpecificationConcoursController::class, 'getRequiredDocuments']);


        Route::post('/inscription-concours', [CandidatController::class, 'inscriptionConcours']);
        Route::get('/mon-profil', [CandidatController::class, 'show']);
        Route::put('/mon-profil', [CandidatController::class, 'update']);
        Route::get('/mes-candidatures', [CandidatController::class, 'getCandidatures']);
        Route::get('/mon-profil-complet', [CandidatController::class, 'getProfilByUtilisateur']);
        Route::get('/ma-fiche-paiement', [CandidatController::class, 'downloadFichePaiement']);
        Route::delete('/mon-profil', [CandidatController::class, 'destroy']);

        Route::prefix('centres')->group(function () {
            Route::get('/', [CentreController::class, 'index']);
            Route::get('/type/{type}', [CentreController::class, 'byType']);
            Route::get('/type/{type}/region/{regionId}', [CentreController::class, 'byTypeAndRegion']);
        });

        Route::get('candidatures', [CandidatureController::class, 'index']);
        Route::post('/candidatures/{id}/rappel', [CandidatureController::class, 'definirRappel']);


        Route::prefix('admin')->middleware('role:ADMINISTRATEUR')->group(function () {

            Route::post('/centres', [CentreController::class, 'store']);
            Route::get('/centres/{id}', [CentreController::class, 'show']);
            Route::put('/centres/{id}', [CentreController::class, 'update']);
            Route::delete('/centres/{id}', [CentreController::class, 'destroy']);

            Route::get('/regions', [RegionController::class, 'index']);

            Route::post('sessions/{id}/etat', [SessionController::class, 'changerEtat']);
            Route::get('sessions/etats', [SessionController::class, 'getEtatSession']);
            Route::apiResource('sessions', SessionController::class);
            Route::apiResource('ecoles', EcoleController::class);

            Route::apiResource('epreuves', AdminEpreuveController::class);

            Route::apiResource('utilisateurs', UtilisateurController::class);
            Route::get('roles', [UtilisateurController::class, 'getAllRoles']);

            Route::post('/concours', [ConcoursController::class, 'store']);
            Route::put('/concours/{id}', [ConcoursController::class, 'update']);
            Route::delete('/concours/{id}', [ConcoursController::class, 'destroy']);
            Route::post('/concours/open', [ConcoursController::class, 'getOpenConcours']);
            Route::get('/concours/avec-candidats', [ConcoursController::class, 'getConcoursAvecCandidats']);


            Route::apiResource('/spec-concours', SpecificationConcoursController::class);

            Route::get('candidatures', [CandidatureController::class, 'getAllForAdmin']);
            Route::get('candidatures/{id}', [CandidatureController::class, 'show']);


            Route::post('candidatures/{id}/valider', [CandidatureController::class, 'valider']);
            Route::post('candidatures/{id}/rejeter', [CandidatureController::class, 'rejeter']);
            Route::put('candidatures/{id}', [CandidatureController::class, 'update']);
            Route::delete('candidatures/{id}', [CandidatureController::class, 'destroy']);
            Route::get('utilisateurs/{userId}/candidatures', [CandidatureController::class, 'getCandidaturesByUser']);

            Route::post('candidatures/{id}/affecter-centres', [CandidatureController::class, 'affecterCentres']);


            Route::get('/dashboard', [DashboardController::class, 'index']);
            Route::get('/dashboard/stats-by-period', [DashboardController::class, 'getStatsByPeriod']);

            Route::get('/concours/export/pdf', [ConcoursController::class, 'exportPdf']);
            Route::get('/concours/export/excel', [ConcoursController::class, 'exportExcel']);

            Route::get('/concours/{id}/export-candidats-pdf', [ConcoursController::class, 'exportCandidatsPdf']);
            Route::get('/concours/{id}/export-candidats-excel', [ConcoursController::class, 'exportCandidatsExcel']);
        });
    });
});


Route::get('/candidature/{id}/fiche/download', [CandidatController::class, 'downloadFicheCandidature'])
    ->name('candidature.download.fiche');
