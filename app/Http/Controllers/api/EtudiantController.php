<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Annonce;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\AnnonceRessource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AnnonceDetailRessource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *      name="Etudiants",
 *     description="Points de terminaison API pour la gestion des Etudiants"
 * )
 */
class EtudiantController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/inscriptionEtudiant",
 *     summary="Enregistrer un nouveau etudiant",
 *     tags={"Etudiants"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"nom", "prenom", "adresse", "telephone", "email", "password", "paysOrigine", "universite", "role", "papierJustificatif"},
 *                 @OA\Property(property="nom", type="string"),
 *                 @OA\Property(property="prenom", type="string"),
 *                 @OA\Property(property="telephone", type="string"),
 *                 @OA\Property(property="email", type="string"),
 *                 @OA\Property(property="password", type="string"),
 *                 @OA\Property(property="paysOrigine", type="string"),
 *                 @OA\Property(property="universite", type="string"),
 *                 @OA\Property(property="adresse", type="string"),
 *                 @OA\Property(property="role", type="string"),
 *                 @OA\Property(property="papierJustificatif", type="file", format="file")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     )
 * )
 */

    public function registerEtudiant(Request $request)
    {
        try {
            $user = new User();
            $validate = Validator::make($request->all(), [
                'nom' => 'required|string|max:255|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ -]+$/|min:2|max:50',
                'prenom' => 'required|string|max:255|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ -]+$/|min:2|max:50',
                'adresse' => 'required|string|min:2|max:100|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ][A-Za-zÀ-ÖØ-öø-ÿ0-9 -]*$/',
                'email' => 'required|unique:users,email|regex:/^[a-zA-Z0-9]+@[a-z]+\.[a-z]{2,6}$/',
                'telephone' => 'required|string|regex:/^[0-9]+$/|unique:users|max:14',
                'paysOrigine' => 'required|string|max:255|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ -]+$/|min:2|max:100',
                'universite' => 'required|string|max:255|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ -]+$/|min:2|max:100',
                'password' => 'required|string|min:8|max:12',
                // 'papierJustificatif' => 'required|file|max:2048',
                'papierJustificatif' => 'required|file|mimetypes:application/pdf,image/jpeg,image/png|max:2048',
                'role' => 'required|string|in:etudiant',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 422);
            }
            $etudiant = new Etudiant();

            $user->nom = $request->input('nom');
            $user->prenom = $request->input('prenom');
            $user->email = $request->input('email');
            $user->telephone = $request->input('telephone');
            $user->role = $request->input('role');
            $user->adresse = $request->input('adresse');
            $user->password = Hash::make($request->password);

            if ($request->hasFile('papierJustificatif')) {
                $diplomePath = $request->file('papierJustificatif')->store('papier_Justificatif', 'public');
                $user->papierJustificatif = $diplomePath;
            }

            $user->save();

            $etudiant->paysOrigine  = $request->input('paysOrigine');
            $etudiant->universite  = $request->input('universite');
            $etudiant->user_id = $user->id;

            if ($etudiant->save()) {
                return response()->json([
                    "message" => "Etudiant enregistré avec succès",
                    "etudiant" => array_merge($etudiant->toArray(), $user->toArray())
                ], 201);
            } else {
                $user->delete();
                return response()->json(["message" => "L'inscription a échoué"]);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }


/**
 * @OA\Post(
 *      path="/api/updateEtudiant",
 *      operationId="updateEtudiant",
 *      tags={"Etudiants"},
 *      summary="Mise à jour des informations de l'étudiant",
 *      description="Permet à l'étudiant de mettre à jour ses informations personnelles.",
 *      @OA\RequestBody(
 *          required=true,
 *          description="Données à mettre à jour",
 *          @OA\MediaType(
 *              mediaType="multipart/form-data",
 *              @OA\Schema(
 *                  required={"nom", "prenom", "adresse", "telephone", "email", "paysOrigine", "universite", "password", "papierJustificatif"},
 *                  @OA\Property(property="nom", type="string", example="Nom"),
 *                  @OA\Property(property="prenom", type="string", example="Prénom"),
 *                  @OA\Property(property="adresse", type="string", example="Adresse"),
 *                  @OA\Property(property="email", type="string", example="etudiant@example.com"),
 *                  @OA\Property(property="telephone", type="string", example="+2217700000"),
 *                  @OA\Property(property="paysOrigine", type="string", example="Pays d'origine"),
 *                  @OA\Property(property="universite", type="string", example="Nom de l'université"),
 *                  @OA\Property(property="password", type="string", example="nouveauMotDePasse"),
 *                  @OA\Property(property="papierJustificatif", type="file", format="file")
 *              )
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Informations mises à jour avec succès.",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Informations mises à jour avec succès"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Étudiant non trouvé.",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Étudiant non trouvé"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=500,
 *          description="Une erreur s'est produite.",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Une erreur s'est produite"),
 *          ),
 *      ),
 *      security={{"bearerAuth": {}}},
 * )
 */



    public function updateEtudiant(Request $request)
    {
        try {
            $user = Auth::user();

            $validate = Validator::make($request->all(), [
                'nom' => 'required|string|max:255|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ -]+$/|min:2|max:50',
                'prenom' => 'required|string|max:255|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ -]+$/|min:2|max:50',
                'adresse' => 'required|string|min:2|max:100|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ][A-Za-zÀ-ÖØ-öø-ÿ0-9 -]*$/',
                'email' => 'required|email|regex:/^[a-zA-Z0-9]+@[a-z]+\.[a-z]{2,6}$/',
                'telephone' => 'required|string|regex:/^[0-9]+$/|min:9|max:14',
                'paysOrigine' => 'required|string|max:255|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ -]+$/|min:2|max:100',
                'universite' => 'required|string|max:255|regex:/^[A-Za-zÀ-ÖØ-öø-ÿ -]+$/|min:2|max:100',
                'password' => 'nullable|string|min:8|max:12',
                'papierJustificatif' => 'nullable|file|mimetypes:application/pdf,image/jpeg,image/png|max:2048',

            ]);
            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 422);
            }
            $user = User::findOrFail($user->id);
            $etudiant = Etudiant::where('user_id', $user->id)->first();

            if (!$etudiant) {
                return response()->json(['error' => 'Étudiant non trouvé'], 404);
            }

            $user->nom = $request->input('nom');
            $user->prenom = $request->input('prenom');
            $user->email = $request->input('email');
            $user->adresse = $request->input('adresse');
            $user->telephone = $request->input('telephone');

            $etudiant->paysOrigine = $request->input('paysOrigine');
            $etudiant->universite = $request->input('universite');

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            if ($request->hasFile('papierJustificatif')) {
                if ($user->papierJustificatif) {
                    Storage::delete($user->papierJustificatif);
                }
                $user->papierJustificatif = $request->file('papierJustificatif')->store('papier_Justificatif', 'public');
            }

            if ($user->save() && $etudiant->save()) {
                return response()->json([
                    "message" => 'Informations mises à jour avec succès',
                ]);
            } else {
                return response()->json(["message" => "La mise à jour a échoué"]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *      path="/api/AnnonceEtudiant",
     *      operationId="getEtudiantAnnonces",
     *      tags={"Etudiants"},
     *      summary="Obtenir la liste des annonces d'un étudiant",
     *      description="Récupère la liste des annonces associés à un étudinat.",
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Liste des annonces récupérée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="annonces", type="array", @OA\Items(type="object")),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Annonces non trouvées",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Annonces non trouvées"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Une erreur s'est produite",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Une erreur s'est produite"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */
    public function indexEtudiant()
    {

        try {
            $user = Auth::user();
            $etudiant = Etudiant::where('user_id', $user->id)->first();

            $annonce = Annonce::where('etudiant_id', $etudiant->id)->get();

            return response()->json([
                AnnonceDetailRessource::collection($annonce),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Annonce non trouvé"], 404);
        } catch (\Exception $e) {
            return response()->json(["message" => "Une erreur s'est produite"], 500);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */


    /**
     * Display the specified resource.
     */
    public function show(Etudiant $etudiant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Etudiant $etudiant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Etudiant $etudiant)
    {
        //
    }
}
