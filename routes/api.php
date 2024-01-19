<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\EtudiantController;
use App\Http\Controllers\api\LocaliteController;
use App\Http\Controllers\api\ProprietaireController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('refresh', 'refresh');
    Route::post('user', 'userInformation');
});

Route::post('inscriptionEtudiant', [EtudiantController::class, 'registerEtudiant']);
Route::post('inscriptionProprietaire', [ProprietaireController::class, 'registerProprietaire']);

Route::post('logout', [AuthController::class, 'logout']);

Route::middleware('auth:api', 'admin')->group(function () {
    Route::put('updateAdmin', [AuthController::class, 'update']);
    Route::put('bloquerUser/{user}', [AuthController::class, 'bloquerUser']);
    Route::put('debloquerUser/{user}', [AuthController::class, 'debloquerUser']);
    Route::get('utilisateursBloques', [AuthController::class, 'listeUtilisateursBloques']);
    Route::get('utilisateurs', [AuthController::class, 'listeUtilisateurs']);
    Route::get('etudiants', [AuthController::class, 'listeEtudiantsNonBloques']);
    Route::get('proprietaires', [AuthController::class, 'listeProprietairesNonBloques']);
    Route::post('ajoutLocalites', [LocaliteController::class, 'store']);
    Route::get('localites', [LocaliteController::class, 'index']);
    Route::put('localites/{id}', [LocaliteController::class, 'update']);
    Route::delete('localites/{id}', [LocaliteController::class, 'destroy']);
});

Route::middleware('auth:api', 'etudiant')->group(function () {
    Route::put('updateEtudiant', [EtudiantController::class, 'updateEtudiant']);
});

Route::middleware('auth:api', 'proprietaire')->group(function () {
    Route::put('updateProprietaire', [ProprietaireController::class, 'updateProprietaire']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
