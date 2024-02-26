<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ImageController;
use App\Http\Controllers\api\AnnonceController;
use App\Http\Controllers\api\EtudiantController;
use App\Http\Controllers\api\LocaliteController;
use App\Http\Controllers\api\LogementController;
use App\Http\Controllers\api\NewsletterController;
use App\Http\Controllers\api\CommentaireController;
use App\Http\Controllers\api\ProprietaireController;
use App\Http\Controllers\api\ForgotPasswordController;

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

Route::get('logements', [LogementController::class, 'index']);
Route::get('annonces', [AnnonceController::class, 'index']);
Route::get('logementParlocalite/{localite}', [LogementController::class, 'logementParLocalite']);
Route::post('newsletter', [NewsletterController::class, 'create']);

Route::post('forget-password', [ForgotPasswordController::class, 'submitForgetPasswordForm'])->name('forget.password.post');
Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('reset.password.get');
Route::post('reset-password', [ForgotPasswordController::class, 'submitResetPasswordForm'])->name('reset.password.post');

Route::post('inscriptionEtudiant', [EtudiantController::class, 'registerEtudiant']);
Route::post('inscriptionProprietaire', [ProprietaireController::class, 'registerProprietaire']);

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('refresh', 'refresh');
    Route::post('user', 'userInformation');
});

Route::middleware('auth:api', 'user')->group(function () {
    Route::post('whatsapp.proprietaire/{id}', [LogementController::class, 'redirigerWhatsApp']);
    Route::get('detailLogement/{id}', [LogementController::class, 'show']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('detailAnnonce/{id}', [AnnonceController::class, 'show']);
    Route::post('gmail.proprietaire/{id}', [LogementController::class, 'redirigerGmail']);
    Route::get('localites', [LocaliteController::class, 'index']);

});

Route::middleware('auth:api', 'admin')->group(function () {
    Route::put('updateAdmin', [AuthController::class, 'update']);
    Route::put('bloquerUser/{user}', [AuthController::class, 'bloquerUser']);
    Route::put('debloquerUser/{user}', [AuthController::class, 'debloquerUser']);
    Route::get('utilisateursBloques', [AuthController::class, 'listeUtilisateursBloques']);
    Route::get('utilisateurs', [AuthController::class, 'listeUtilisateurs']);
    Route::get('etudiants', [AuthController::class, 'listeEtudiantsNonBloques']);
    Route::get('proprietaires', [AuthController::class, 'listeProprietairesNonBloques']);
    Route::post('ajoutLocalites', [LocaliteController::class, 'store']);
    Route::put('localites/{id}', [LocaliteController::class, 'update']);
    Route::put('validerInscription/{userId}', [AuthController::class, 'validerInscription']);
    Route::put('rejeterInscription/{userId}', [AuthController::class, 'rejeterInscription']);
    Route::delete('localites/{id}', [LocaliteController::class, 'destroy']);
    Route::get('logementsAdmin', [LogementController::class, 'index']);
    Route::delete('supprimerCommentaires/{id}', [CommentaireController::class, 'destroyByAdmin']);
    Route::delete('supprimerAnnonces/{id}', [AnnonceController::class, 'destroyByAdmin']);
    Route::get('listeNewsletter', [NewsletterController::class, 'index']);

});

Route::middleware('auth:api', 'etudiant')->group(function () {
    Route::put('updateEtudiant', [EtudiantController::class, 'updateEtudiant']);
    Route::post('ajoutCommentaire', [CommentaireController::class, 'store']);
    Route::put('commentaires/{commentaire}', [CommentaireController::class, 'update']);
    Route::delete('commentaires/{id}', [CommentaireController::class, 'destroy']);
    Route::post('ajoutAnnonce', [AnnonceController::class, 'store']);
    Route::put('annonces/{annonce}', [AnnonceController::class, 'update']);
    Route::delete('annonces/{id}', [AnnonceController::class, 'destroy']);
    Route::put('marquerPrisEncharge/{id}', [AnnonceController::class, 'marquerPriseEnCharge']);
    Route::get('AnnonceEtudiant', [EtudiantController::class, 'indexEtudiant']);
});

Route::middleware('auth:api', 'proprietaire')->group(function () {
    Route::put('updateProprietaire', [ProprietaireController::class, 'updateProprietaire']);
    Route::post('ajoutLogements', [LogementController::class, 'store']);
    Route::post('logements/{id}', [LogementController::class, 'update']);
    Route::delete('logements/{id}', [LogementController::class, 'destroy']);
    Route::get('logementsProprietaire', [ProprietaireController::class, 'index']);
    Route::post('ajoutImage/{id}', [ImageController::class, 'addImage']);
    Route::delete('supprimerImmage/{logementId}/{imageId}', [ImageController::class, 'deleteImage']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
