<?php

use App\Http\Controllers\Admin\CandidatureController;
use App\Http\Controllers\Admin\CentreController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\api\CandidatController;
use App\Http\Controllers\Api\ConcoursController;
use App\Http\Controllers\Api\EcoleController;
use App\Http\Controllers\Api\EpreuveController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\SpecificationConcoursController;
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
    Route::get('/concours/{id}', [ConcoursController::class, 'show']);


    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

        Route::post('/concours', [ ConcoursController::class, 'store']);
        Route::put('/concours/{id}', [ConcoursController::class, 'update']);
        Route::delete('/concours/{id}', [ConcoursController::class, 'destroy']);
        Route::post('/concours/open', [ConcoursController::class, 'getOpenConcours']);

        Route::apiResource('/spec-concours', SpecificationConcoursController::class);
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

        Route::prefix('admin')->middleware('role:ADMINISTRATEUR')->group(function () {

            Route::apiResource('sessions', SessionController::class);
            Route::apiResource('ecoles', EcoleController::class);

            Route::apiResource('epreuves', EpreuveController::class);

            Route::get('candidatures', [CandidatureController::class, 'index']);
            Route::get('candidatures/{id}', [CandidatureController::class, 'show']);

            Route::post('candidatures/{id}/valider', [CandidatureController::class, 'valider']);
            Route::post('candidatures/{id}/rejeter', [CandidatureController::class, 'rejeter']);

            Route::put('candidatures/{id}/centres', [CandidatureController::class, 'affecterCentres']);
        });

    });
});


Route::get('/candidature/{id}/fiche/download', [CandidatController::class, 'downloadFicheCandidature'])
    ->name('candidature.download.fiche');
