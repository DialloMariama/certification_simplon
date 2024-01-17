<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\EtudiantController;
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

    
});

Route::middleware('auth:api', 'etudiant')->group(function () {
    
});

Route::middleware('auth:api', 'proprietaire')->group(function () {
    
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
