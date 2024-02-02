<?php

namespace App\Http\Controllers\api;

use App\Models\Localite;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *      name="Localites",
 *     description="Points de terminaison API pour la gestion des localités"
 * )
 */
class LocaliteController extends Controller
{

    /**
     * @OA\Get(
     *      path="/api/localites",
     *      operationId="getLocalites",
     *      tags={"Localites"},
     *      summary="Obtenir la liste de toutes les localités",
     *      description="Retourne la liste de toutes les localités enregistrées.",
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Liste de toutes les localités",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Liste de toutes les localités"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Non autorisé",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Non autorisé"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */
    public function index()
    {
        return response()->json([
            'message' => 'Liste de toutes les localités',
            'localite' => Localite::all()
        ]);
    }


    /**
     * @OA\Post(
     *      path="/api/ajoutLocalites",
     *      operationId="createLocalite",
     *      tags={"Localites"},
     *      summary="Enregistrer une nouvelle localité",
     *      description="Enregistre une nouvelle localité associée à l'utilisateur authentifié.",
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Contenu de la requête",
     *          @OA\JsonContent(
     *              @OA\Property(property="nomLocalite", type="string", example="Nom de la localité"),
     *              @OA\Property(property="commune", type="string", example="Nom de la commune"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Localité enregistrée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Localité enregistrée avec succès"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Non autorisé",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Non autorisé"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Erreur de validation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Erreur de validation"),
     *              @OA\Property(property="errors", type="object"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */


    public function store(Request $request)
    {
        $user = Auth::user();

        $validate= Validator::make($request->all(),[
            'nomLocalite' => 'required|string|max:255',
            'commune' => 'required|string|max:255',
        ]);
        if($validate->fails()){
            return response()->json([
                'error' => $validate->errors()
            ], 422);
        }
        $localite = Localite::create([
            'nomLocalite' => $request->nomLocalite,
            'user_id' => $user->id,
            'commune' => $request->commune,
        ]);
        return response()->json([
            'message' => 'Localité enregistrée avec succés',
            'localite' => $localite
        ]);
    }

    /**
     * @OA\Put(
     *      path="/api/localites/{id}",
     *      operationId="updateLocalite",
     *      tags={"Localites"},
     *      summary="Modifier une localité existante",
     *      description="Modifie une localité existante associée à l'utilisateur authentifié.",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la localité à modifier",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Contenu de la requête",
     *          @OA\JsonContent(
     *              @OA\Property(property="nomLocalite", type="string", example="Nouveau nom de la localité"),
     *              @OA\Property(property="commune", type="string", example="Nouveau nom de la commune"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Localité modifiée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Localité modifiée avec succès"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Non autorisé",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Non autorisé"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Localité non trouvée",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Localité non trouvée"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Erreur de validation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Erreur de validation"),
     *              @OA\Property(property="errors", type="object"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */
    public function update(Request $request, $id)
    {
        $localite = Localite::findOrFail($id);
  
        $validate= Validator::make($request->all(),[
            'nomLocalite' => 'required|string|max:255',
            'commune' => 'required|string|max:255',
        ]);
        if($validate->fails()){
            return response()->json([
                'error' => $validate->errors()
            ], 422);
        }
        $localite->nomLocalite = $request->input('nomLocalite');
        $localite->commune = $request->input('commune');

        if ($localite->update()) {
            return response()->json([
                'localite' => $localite,
                'message' => 'Localité modifiée',
            ]);
        } else {
            return response()->json([
                'message' => 'Localité non modifiée',
            ], 404);
        }
    }


    /**
     * Remove the specified resource from storage.
     */

    /**
     * @OA\Delete(
     *      path="/api/localites/{id}",
     *      operationId="deleteLocalite",
     *      tags={"Localites"},
     *      summary="Supprimer une localité existante",
     *      description="Supprime une localité existante associée à l'utilisateur authentifié.",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de la localité à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Localité supprimée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Localité supprimée avec succès"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Non autorisé",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Non autorisé"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Localité non trouvée",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Localité non trouvée"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * )
     */
    public function destroy($id)
    {
        $localite = Localite::findOrFail($id);
        if ($localite->delete()) {
            return response()->json([
                'message' => 'Localité supprimée',
            ]);
        } else {
            return response()->json([
                'message' => 'Localité non supprimée',
            ], 404);
        }
    }
}
