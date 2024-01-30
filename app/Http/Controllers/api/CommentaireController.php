<?php

namespace App\Http\Controllers\api;


use App\Models\Logement;
use App\Models\Commentaire;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCommentaireRequest;
use App\Http\Requests\UpdateCommentaireRequest;
use App\Models\Etudiant;

/**
 * @OA\Tag(
 *      name="Commentaires",
 *     description="Points de terminaison API pour la gestion des commentaires"
 * )
 */
class CommentaireController extends Controller
{
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
     * @OA\Post(
     *      path="/api/ajoutCommentaire",
     *      operationId="createCommentaire",
     *      tags={"Commentaires"},
     *      summary="Créer un commentaire pour un logement",
     *      description="Crée un nouveau commentaire pour un logement donné.",
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="Données du commentaire",
     *          @OA\JsonContent(
     *              required={"texte", "logement_id"},
     *              @OA\Property(property="texte", type="string"),
     *              @OA\Property(property="logement_id", type="integer"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Commentaire créé avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Commentaire créé avec succès"),
     *              @OA\Property(property="commentaire", type="object"),
     *              @OA\Property(property="logement", type="object"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Logement non trouvé",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Logement non trouvé"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation failed",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object"),
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
    public function store(Request $request)
    {
        $request->validate([
            'texte' => 'required|string',
            'logement_id' => 'required|numeric',
        ]);
        $etudiant = Etudiant::where('user_id', Auth::user()->id)->first();
        $commentaire = new Commentaire();
        $commentaire->texte = $request->input('texte');
        $commentaire->etudiant_id = $etudiant->id;

        $logement = Logement::findOrFail($request->input('logement_id'));

        $logement->commentaires()->save($commentaire);

        return response()->json([
            'message' => 'Commentaire créé avec succès',
            'commentaire' => $commentaire,
            'logement' => $logement,

        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Commentaire $commentaire)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Commentaire $commentaire)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * @OA\Put(
     *      path="/api/commentaires/{commentaire}",
     *      operationId="updateCommentaire",
     *      tags={"Commentaires"},
     *      summary="Mettre à jour un commentaire",
     *      description="Met à jour un commentaire existant pour un étudiant authentifié.",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="commentaire",
     *          description="ID du commentaire à mettre à jour",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Nouvelles données du commentaire",
     *          @OA\JsonContent(
     *              required={"texte"},
     *              @OA\Property(property="texte", type="string"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Commentaire mis à jour avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Commentaire mis à jour avec succès"),
     *              @OA\Property(property="commentaire", type="object"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Permission refusée",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Vous n'avez pas la permission de mettre à jour ce commentaire."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Commentaire non trouvé",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Commentaire non trouvé"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Une erreur s'est produite lors de la mise à jour du commentaire",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Une erreur s'est produite lors de la mise à jour du commentaire"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */

    public function update(Request $request, Commentaire $commentaire)
    {
        try {
            $request->validate([
                'texte' => 'required|string',
            ]);
            $etudiant = Etudiant::where('user_id', Auth::user()->id)->first();

            if ($etudiant && $etudiant->id === $commentaire->etudiant_id) {
                $commentaire->texte = $request->input('texte');
                $commentaire->update();

                return response()->json([
                    'message' => 'Commentaire mis à jour avec succès',
                    'commentaire' => $commentaire,
                ]);
            } else {
                return response()->json(['error' => 'Vous n\'avez pas la permission de mettre à jour ce commentaire.'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Une erreur s\'est produite lors de la mise à jour du commentaire'], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */

    /**
     * @OA\Delete(
     *      path="/api/commentaires/{id}",
     *      operationId="deleteCommentaire",
     *      tags={"Commentaires"},
     *      summary="Supprimer un commentaire",
     *      description="Supprime un commentaire pour un étudiant authentifié.",
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du commentaire à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Commentaire supprimé avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Commentaire supprimé"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Permission refusée",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Vous n'avez pas la permission de supprimer ce commentaire."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Commentaire non trouvé",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Commentaire non trouvé"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $etudiant = Etudiant::where('user_id', $user->id)->first();
        $commentaire = $etudiant->commentaires()->find($id);

        if (!$commentaire) {
            return response()->json(['error' => 'Vous n\'avez pas la permission de supprimer ce commentaire.'], 403);
        }
        if ($commentaire->delete()) {
            return response()->json([
                'message' => 'Commentaire supprimé',
            ]);
        } else {
            return response()->json([
                'message' => 'Commentaire non supprimé',
            ], 404);
        }
    }
  

    /**
 * @OA\Delete(
 *      path="/api/supprimerCommentaires/{id}",
 *      operationId="deleteCommentaire",
 *      tags={"Commentaires"},
 *      summary="Supprimer un commentaire par un admin",
 *      description="Supprime un commentaire en fonction de l'ID fourni.",
 *      @OA\Parameter(
 *          name="id",
 *          description="ID du commentaire à supprimer",
 *          required=true,
 *          in="path",
 *          @OA\Schema(type="integer", format="int64"),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Commentaire supprimé",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Commentaire supprimé"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Vous n'avez pas la permission de supprimer ce commentaire",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Vous n'avez pas la permission de supprimer ce commentaire."),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Commentaire non trouvé",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Commentaire non trouvé"),
 *          ),
 *      ),
 *      security={{"bearerAuth": {}}},
 * )
 */
    public function destroyByAdmin($id)
    {
        try {
            // Vérifier si l'utilisateur actuel est un administrateur
            if (!Auth::user()->isAdmin()) {
                return response()->json(['error' => 'Vous n\'avez pas la permission de supprimer ce commentaire.'], 403);
            }

            $commentaire = Commentaire::find($id);

            if (!$commentaire) {
                return response()->json(['error' => 'Commentaire non trouvé'], 404);
            }

            $commentaire->delete();

            return response()->json([
                'message' => 'Commentaire supprimé par l\'administrateur',
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Une erreur s\'est produite'], 500);
        }
    }

}
