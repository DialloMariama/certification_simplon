<?php

namespace App\Http\Controllers\api;

use App\Models\Image;


use App\Models\Logement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreImageRequest;
use App\Http\Requests\UpdateImageRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *      name="Images",
 *     description="Points de terminaison API pour la gestion des images"
 * )
 */
class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *      path="/api/ajoutImage/{id}",
     *      operationId="addImage",
     *      tags={"Images"},
     *      summary="Ajouter une image à un logement",
     *      description="Ajoute une image à un logement spécifié par l'ID.",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du logement",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer", format="int64"),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Données de l'image",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="image[]",
     *                      type="array",
     *                      @OA\Items(type="string", format="binary"),
     *                      description="Fichier image à téléverser",
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Image ajoutée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Image ajoutée avec succès"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Permission refusée",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Vous n'avez pas la permission d'ajouter une image à ce logement."),
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
     *          response=500,
     *          description="Erreur interne du serveur",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Une erreur s'est produite"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * )
     */
    public function addImage(Request $request, $id)
    {
        try {
            $logement = Logement::findOrFail($id);

            $user = Auth::user()->proprietaires()->first();

            if ($user->id != $logement->proprietaire_id) {
                return response()->json(['error' => 'Vous n\'avez pas la permission d\'ajouter une image à ce logement.'], 403);
            }
            $validate= Validator::make($request->all(),[
                'image.*' => 'required|file',

            ]);
            if($validate->fails()){
                return response()->json([
                    'error' => $validate->errors()
                ]);
            }
            $imagesData = [];


            if ($request->file('image')) {
                foreach ($request->file('image') as $file) {
                    $image = new Image();
                    $imagePath = $file->store('images/logement', 'public');
                    $image->nomImage = $imagePath;
                    $image->logement_id = $logement->id;
                    $image->save();
                    $imagesData =  $image;
                }
            }

            return response()->json([
                "message" => "Image ajoutée avec succès",
                "image" => $imagesData,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Logement non trouvé"], 404);
        } catch (\Exception $e) {
            return response()->json(["message" => "Une erreur s'est produite"], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreImageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Image $image)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Image $image)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateImages(Request $request, $logementId)
    {
        try {
            $user = Auth::user();
            // dd(Auth::user());

            $logement = Logement::where('id', $logementId)
                ->where('proprietaire_id', $user->proprietaire->id)
                ->firstOrFail();

            if ($request->has('remplacer_images') && $request->remplacer_images) {
                $logement->images()->delete();
            }

            $validate= Validator::make($request->all(),[
                'image.*' => 'required|file',

            ]);
            if($validate->fails()){
                return response()->json([
                    'error' => $validate->errors()
                ]);
            }

            foreach ($request->file('image') as $file) {
                $image = new Image();
                $imagePath = $file->store('images/logement', 'public');
                $image->nomImage = $imagePath;
                $image->logement_id = $logement->id;
                $image->save();
            }

            return response()->json([
                "message" => "Images du logement mises à jour avec succès",
                "logement" => $logement->load('images'),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Logement non trouvé"], 404);
        } catch (\Exception $e) {
            return response()->json(["message" => "Une erreur s'est produite"], 500);
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/supprimerImmage/{logementId}/{imageId}",
     *      operationId="deleteImage",
     *      tags={"Images"},
     *      summary="Supprimer une image d'un logement",
     *      description="Supprime une image spécifiée par l'ID d'un logement spécifié par l'ID.",
     *      @OA\Parameter(
     *          name="logementId",
     *          description="ID du logement",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer", format="int64"),
     *      ),
     *      @OA\Parameter(
     *          name="imageId",
     *          description="ID de l'image",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer", format="int64"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Image supprimée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Image supprimée avec succès"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Permission refusée",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Vous n'avez pas la permission de supprimer cette image."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Logement ou image non trouvé",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Logement ou image non trouvé"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur interne du serveur",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Une erreur s'est produite"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */
    public function deleteImage($logementId, $imageId)
    {
        try {
            $logement = Logement::findOrFail($logementId);
            $user = Auth::user()->proprietaires()->first();


            if ($user->id != $logement->proprietaire_id) {
                return response()->json(['error' => 'Vous n\'avez pas la permission d\'ajouter une image à ce logement.'], 403);
            }

            $image = Image::findOrFail($imageId);

            // Supprimer l'image du stockage
            Storage::disk('public')->delete($image->nomImage);

            // Supprimer l'entrée de la base de données
            $image->delete();

            return response()->json([
                "message" => "Image supprimée avec succès",
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Logement ou image non trouvé"], 404);
        } catch (\Exception $e) {
            return response()->json(["message" => "Une erreur s'est produite"], 500);
        }
    }
}
