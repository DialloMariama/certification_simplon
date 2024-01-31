<?php

namespace App\Http\Controllers\api;

use App\Models\Annonce;


use App\Models\Etudiant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *      name="Annonces",
 *     description="Points de terminaison API pour la gestion des annonces"
 * )
 */
class AnnonceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *      path="/api/annonces",
     *      operationId="getAnnonces",
     *      tags={"Annonces"},
     *      summary="Liste de toutes les annonces",
     *      description="Récupère la liste de toutes les annonces non encore prises en charge.",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des annonces récupérée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="status_code", type="integer", example=200),
     *              @OA\Property(property="message", type="string", example="Liste de toutes les annonces"),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Annonce")),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur interne du serveur",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Une erreur s'est produite"),
     *          ),
     *      ),
     * )
     */
    public function index()
    {
        $annonces = Annonce::where('prisEnCharge', false)->get();

        return response()->json([
            'status_code' => 200,
            'message' => 'Liste de toutes les annonces',
            'data' => $annonces,
        ]);
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
     * @OA\Post(
     *      path="/api/ajoutAnnonce",
     *      operationId="createAnnonce",
     *      tags={"Annonces"},
     *      summary="Créer une nouvelle annonce",
     *      description="Permet à un étudiant de créer une nouvelle annonce.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="description", type="string", example="Description de l'annonce"),
     *              @OA\Property(property="budget", type="number", format="float", example=1000.00),
     *              @OA\Property(property="caracteristiques", type="string", example="Caractéristiques de l'annonce"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Annonce créée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Annonce créée avec succès"),
     *              @OA\Property(property="annonce", ref="#/components/schemas/Annonce"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation a échoué",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Les données de la requête sont invalides."),
     *              @OA\Property(property="errors", type="object", example={"description": {"Le champ description est requis."}}),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */
    public function store(Request $request)
    {
       $validate= Validator::make($request->all(),[
            'description' => 'required|string',
            'budget' => 'required|numeric',
            'caracteristiques' => 'required|string',
        ]);
        if($validate->fails()){
            return response()->json([
                'error' => $validate->errors()
            ]);
        }

        $etudiant = Etudiant::where('user_id', Auth::user()->id)->first();
        $annonce = new Annonce();
        $annonce->description = $request->input('description');
        $annonce->caracteristiques = $request->input('caracteristiques');
        $annonce->budget = $request->input('budget');
        $annonce->etudiant_id = $etudiant->id;
        $annonce->save();

        return response()->json([
            'message' => 'Annonce créé avec succès',
            'annonce' => $annonce,
        ]);
    }

    /**
     * Display the specified resource.
     */

    /**
     * @OA\Get(
     *      path="/api/detailAnnonce/{id}",
     *      operationId="getAnnonceById",
     *      tags={"Annonces"},
     *      summary="Obtenir une annonce par son identifiant",
     *      description="Récupère une annonce spécifique en utilisant son identifiant.",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID de l'annonce",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Annonce récupérée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="Annonce", ref="#/components/schemas/Annonce"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Annonce non trouvée",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Annonce non trouvé"),
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
    public function show($id)
    {
        try {
            $annonce = Annonce::findOrFail($id);

            return response()->json([
                "Annonce" => $annonce,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Annonce non trouvé"], 404);
        } catch (\Exception $e) {
            return response()->json(["message" => "Une erreur s'est produite"], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Annonce $annonce)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * @OA\Put(
     *      path="/api/annonces/{annonce}",
     *      operationId="updateAnnonce",
     *      tags={"Annonces"},
     *      summary="Mettre à jour une annonce",
     *      description="Met à jour une annonce spécifique en utilisant son identifiant.",
     *      @OA\Parameter(
     *          name="annonce",
     *          description="Objet de l'annonce à mettre à jour",
     *          required=true,
     *          in="path",
     *          @OA\Schema(ref="#/components/schemas/Annonce"),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="budget", type="number"),
     *              @OA\Property(property="caracteristiques", type="string"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Annonce mise à jour avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Annonce mise à jour avec succès"),
     *              @OA\Property(property="annonce", ref="#/components/schemas/Annonce"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Vous n'avez pas la permission de mettre à jour cette annonce",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Vous n'avez pas la permission de mettre à jour cette annonce"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Une erreur s'est produite lors de la mise à jour de l'annonce",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Une erreur s'est produite lors de la mise à jour de l'annonce"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */

    public function update(Request $request, Annonce $annonce)
    {
        try {
            $validate= Validator::make($request->all(),[
                'description' => 'required|string',
                'budget' => 'required|numeric',
                'caracteristiques' => 'required|string',
            ]);
            if($validate->fails()){
                return response()->json([
                    'error' => $validate->errors()
                ]);
            }
            $etudiant = Etudiant::where('user_id', Auth::user()->id)->first();

            if ($etudiant && $etudiant->id === $annonce->etudiant_id) {
                $annonce->description = $request->input('description');
                $annonce->caracteristiques = $request->input('caracteristiques');
                $annonce->budget = $request->input('budget');
                $annonce->etudiant_id = $etudiant->id;
                $annonce->update();

                return response()->json([
                    'message' => 'Annonce mise à jour avec succès',
                    'anno$annonce' => $annonce,
                ]);
            } else {
                return response()->json(['error' => 'Vous n\'avez pas la permission de mettre à jour cette annonce.'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Une erreur s\'est produite lors de la mise à jour de l\'annonce'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */


    /**
     * @OA\Delete(
     *      path="/api/annonces/{id}",
     *      operationId="deleteAnnonce",
     *      tags={"Annonces"},
     *      summary="Supprimer une annonce",
     *      description="Supprime une annonce spécifique en utilisant son identifiant.",
     *      @OA\Parameter(
     *          name="id",
     *          description="Identifiant de l'annonce à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Annonce supprimée",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Annonce supprimée"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Vous n'avez pas la permission de supprimer cette annonce",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Vous n'avez pas la permission de supprimer cette annonce"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Annonce non supprimée",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Annonce non supprimée"),
     *          ),
     *      ),
     *
     *      security={{"bearerAuth": {}}},
     * )
     */

    public function destroy($id)
    {
        $user = Auth::user();
        $etudiant = Etudiant::where('user_id', $user->id)->first();
        $annonce = $etudiant->annonces()->find($id);

        if (!$annonce) {
            return response()->json(['error' => 'Vous n\'avez pas la permission de supprimer cette annonce.'], 403);
        }
        if ($annonce->delete()) {
            return response()->json([
                'message' => 'Annonce supprimée',
            ]);
        } else {
            return response()->json([
                'message' => 'Annonce non supprimée',
            ], 404);
        }
    }


    /**
     * @OA\Put(
     *      path="/api/marquerPrisEncharge/{id}",
     *      operationId="marquerPriseEnCharge",
     *      tags={"Annonces"},
     *      summary="Marquer une annonce comme prise en charge",
     *      description="Marque une annonce spécifique comme prise en charge par un étudiant.",
     *      @OA\Parameter(
     *          name="id",
     *          description="Identifiant de l'annonce à marquer comme prise en charge",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Annonce marquée comme prise en charge avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Annonce marquée comme prise en charge avec succès"),
     *              @OA\Property(property="annonce", ref="#/components/schemas/Annonce"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Vous n'avez pas la permission de marquer cette annonce",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Vous n'avez pas la permission de marquer cette annonce"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Annonce non trouvée",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Annonce non trouvée"),
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

    public function marquerPriseEnCharge($id)
    {
        try {
            $user = Auth::user();
            $etudiant = Etudiant::where('user_id', $user->id)->first();
            $annonce = $etudiant->annonces()->findOrFail($id);
            $annonce->update([
                'prisEnCharge' => true,
            ]);

            return response()->json([
                "message" => "Annonce marquée comme prise en charge avec succès",
                "annonce" => $annonce,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["error" => "Vous n'avez pas la permission de marquer cette annonce."], 403);
        } catch (\Exception $e) {
            return response()->json(["message" => "Une erreur s'est produite"], 500);
        }
    }


 /**
 * @OA\Delete(
 *      path="/api/supprimerAnnonces/{id}",
 *      operationId="deleteAnnonce",
 *      tags={"Annonces"},
 *      summary="Supprimer une annonce par un admin",
 *      description="Supprime une annonce en fonction de l'ID fourni.",
 *      @OA\Parameter(
 *          name="id",
 *          description="ID de l'annonce à supprimer",
 *          required=true,
 *          in="path",
 *          @OA\Schema(type="integer", format="int64"),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Annonce supprimée",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Annonce supprimée"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Vous n'avez pas la permission de supprimer cette annonce",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Vous n'avez pas la permission de supprimer cette annonce."),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Annonce non trouvée",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Annonce non trouvée"),
 *          ),
 *      ),
 *      security={{"bearerAuth": {}}},
 * )
 */
    public function destroyByAdmin($id)
    {
        try {
            if (!Auth::user()->isAdmin()) {
                return response()->json(['error' => 'Vous n\'avez pas la permission de supprimer cette annonce.'], 403);
            }

            $annonce = Annonce::find($id);

            if (!$annonce) {
                return response()->json(['error' => 'annonce non trouvée'], 404);
            }

            $annonce->delete();

            return response()->json([
                'message' => 'Annonce supprimée par l\'administrateur',
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Une erreur s\'est produite'], 500);
        }
    }
}
