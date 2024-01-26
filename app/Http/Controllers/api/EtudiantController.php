<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
     *     path="/api/registerEtudiant",
     *     summary="Enregistrer un nouveau etudiant",
     *     tags={"Etudiants"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom", "prenom", "adresse", "telephone", "email", "password", "paysOrigine", "universite", "role"},
     *             @OA\Property(property="nom", type="string"),
     *             @OA\Property(property="prenom", type="string"),
     *             @OA\Property(property="telephone", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="paysOrigine", type="string"),
     *             @OA\Property(property="universite", type="string"),
     *             @OA\Property(property="role", type="string")
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
        // dd($request->all());
        try {
            $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'telephone' => 'required|numeric|regex:/^\+[0-9]+$/|max:14',
                'paysOrigine' => 'required|string|max:50',
                'universite' => 'required|string|max:100',
                'role' => 'required|string|in:etudiant',
            ]);

            $user = new User();
            $etudiant = new Etudiant();

            $user->nom = $request->input('nom');
            $user->prenom = $request->input('prenom');
            $user->email = $request->input('email');
            $user->telephone = $request->input('telephone');
            $user->role = $request->input('role');
            $user->adresse = $request->input('adresse');
            $user->password = Hash::make($request->password);

            $user->save();

            $etudiant->paysOrigine  = $request->input('paysOrigine');
            $etudiant->universite  = $request->input('universite');
            $etudiant->user_id = $user->id;

            if ($etudiant->save()) {
                return response()->json([
                    "message" => "Etudiant enregistré avec success",
                    "etudiant" => array_merge(array($etudiant), array($user))
                ]);
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
     * @OA\Put(
     *      path="/api/updateEtudiant",
     *      operationId="updateEtudiant",
     *      tags={"Etudiants"},
     *      summary="Mise à jour des informations de l'étudiant",
     *      description="Permet à l'étudiant de mettre à jour ses informations personnelles.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Données à mettre à jour",
     *          @OA\JsonContent(
     *              @OA\Property(property="nom", type="string", example="Nom"),
     *              @OA\Property(property="prenom", type="string", example="Prénom"),
     *              @OA\Property(property="adresse", type="string", example="Adresse"),
     *              @OA\Property(property="email", type="string", example="etudiant@example.com"),
     *              @OA\Property(property="telephone", type="string", example="+2217700000"),
     *              @OA\Property(property="paysOrigine", type="string", example="Pays d'origine"),
     *              @OA\Property(property="universite", type="string", example="Nom de l'université"),
     *              @OA\Property(property="password", type="string", example="nouveauMotDePasse"),
     *          ),
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

            $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'telephone' => 'nullable|numeric|regex:/^\+[0-9]+$/|max:14',
                'paysOrigine' => 'required|string|max:50',
                'universite' => 'required|string|max:100',
            ]);

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
    public function index()
    {
        //
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
