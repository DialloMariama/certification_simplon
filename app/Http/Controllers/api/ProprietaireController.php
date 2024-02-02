<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Localite;
use App\Models\Logement;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\LogementRessource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreProprietaireRequest;
use App\Http\Requests\UpdateProprietaireRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *      name="Propriétaires",
 *     description="Points de terminaison API pour la gestion des proprietaires"
 * )
 */

class ProprietaireController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/inscriptionProprietaire",
     *     summary="Enregistrer un nouveau proprietaire",
     *     tags={"Propriétaires"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom", "prenom", "adresse", "telephone", "email", "password", "role"},
     *             @OA\Property(property="nom", type="string"),
     *             @OA\Property(property="prenom", type="string"),
     *             @OA\Property(property="telephone", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *              @OA\Property(property="adresse", type="string", example="Adresse"),
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
    public function registerProprietaire(Request $request)
    {
        try {

            $validate = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'telephone' => 'required|string|regex:/^\+[0-9]+$/|unique:users|max:14',
                'role' => 'required|string|in:proprietaire',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 422);
            }


            $user = new User();
            $proprietaire = new Proprietaire();

            $user->nom = $request->input('nom');
            $user->prenom = $request->input('prenom');
            $user->email = $request->input('email');
            $user->telephone = $request->input('telephone');
            $user->role = $request->input('role');
            $user->adresse = $request->input('adresse');
            $user->password = Hash::make($request->password);

            $user->save();
            $proprietaire->user_id = $user->id;

            if ($proprietaire->save()) {
                return response()->json([
                    "message" => "Propriétaire enregistré avec succés",
                    "proprietaire" => array_merge(array($proprietaire), array($user))
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
     *      path="/api/updateProprietaire",
     *      operationId="updateProprietaire",
     *      tags={"Propriétaires"},
     *      summary="Mise à jour des informations du propriétaire",
     *      description="Permet au propriétaire de mettre à jour ses informations personnelles.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Données à mettre à jour",
     *          @OA\JsonContent(
     *              @OA\Property(property="nom", type="string", example="Nom"),
     *              @OA\Property(property="prenom", type="string", example="Prénom"),
     *              @OA\Property(property="adresse", type="string", example="Adresse"),
     *              @OA\Property(property="email", type="string", example="proprietaire@example.com"),
     *              @OA\Property(property="telephone", type="string", example="+1234567890"),
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
     *          description="Propriétaire non trouvé.",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Propriétaire non trouvé"),
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

    public function updateProprietaire(Request $request)
    {
        try {
            $user = Auth::user();

            $validate = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'telephone' => 'nullable|string|regex:/^\+[0-9]+$/|unique:users|max:14',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 422);
            }
            $user = User::findOrFail($user->id);
            $proprietaire = Proprietaire::where('user_id', $user->id)->first();
            if (!$proprietaire) {
                return response()->json(['error' => 'Étudiant non trouvé'], 404);
            }

            $user->nom = $request->input('nom', $user->nom);
            $user->prenom = $request->input('prenom', $user->prenom);
            $user->email = $request->input('email', $user->email);
            $user->adresse = $request->input('adresse', $user->adresse);
            $user->telephone = $request->input('telephone', $user->telephone);

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            if ($user->save() && $proprietaire->save()) {
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
     * @OA\Get(
     *      path="/api/logementsProprietaire",
     *      operationId="getProprietaireLogements",
     *      tags={"Propriétaires"},
     *      summary="Obtenir la liste des logements d'un propriétaire",
     *      description="Récupère la liste des logements associés à un propriétaire.",
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Liste des logements récupérée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="logements", type="array", @OA\Items(type="object")),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Logements non trouvés",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Logements non trouvés"),
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

    public function index()
    {

        try {
            $user = Auth::user();
            $user = Auth::user();
            $proprietaire = Proprietaire::where('user_id', $user->id)->first();

            // $logement = Logement::where('proprietaire_id', $proprietaire->id)->get();

            $logement = Logement::with(['commentaires', 'images', 'proprietaire'])->where('proprietaire_id', $proprietaire->id)->get();

            return response()->json([
                LogementRessource::collection($logement),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Logement non trouvé"], 404);
        } catch (\Exception $e) {
            return response()->json(["message" => "Une erreur s'est produite"], 500);
        }
    }
}
