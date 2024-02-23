<?php

namespace App\Http\Controllers\api;

use Exception;

use App\Models\User;
use App\Models\Image;
use App\Models\Localite;
use App\Models\Logement;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\LogementRessource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\LogementDetailRessource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *      name="Logements",
 *     description="Points de terminaison API pour la gestion des logements et images"
 * )
 */
class LogementController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *      path="/api/logements",
     *      operationId="getAllLogements",
     *      tags={"Logements"},
     *      summary="Récupérer la liste de tous les logements",
     *      description="Récupère la liste de tous les logements avec leurs commentaires et une seule image associée à chaque logement.",
     *      @OA\Response(
     *          response=200,
     *          description="Liste de tous les logements récupérée avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Liste de tous les logements récupérée avec succès"),
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
     * )
     */
    public function index()
    {
        try {
            $logement = Logement::with(['commentaires', 'images', 'proprietaire'])->get();

            return response()->json([
                LogementRessource::collection($logement),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Logement non trouvé"], 404);
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
     * @OA\Post(
     *     path="/api/ajoutLogements",
     *     summary="Ajout d'une annonce",
     *     security={
     *         {"bearerAuth": {}}
     *     },
     *     tags={"Logements"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *              @OA\Property(property="adresse", type="string"),
 *                  @OA\Property(property="type", type="string"),
 *                  @OA\Property(property="disponibilite", type="string", format="date"),
 *                  @OA\Property(property="description", type="string"),
 *                  @OA\Property(property="superficie", type="number"),
 *                  @OA\Property(property="prix", type="number"),
 *                  @OA\Property(property="nombreChambre", type="integer"),
 *                  @OA\Property(property="equipements", type="string"),
 *                  @OA\Property(property="localite_id", type="integer"),
     *            @OA\Property(property="image[]",type="array",@OA\Items(type="string", format="binary"),description="Liste de fichiers images")
     *         )
     *        )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="annonce ajoutée avec succées",
     *     ),
     *     @OA\Response(response=401, description="Validation Error")
     * )
     */

    public function store(Request $request)

    {
        try {
            $validate = Validator::make($request->all(), [
                'adresse' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'disponibilite' => 'required|date',
                'description' => 'required|string',
                'superficie' => 'required|numeric',
                'prix' => 'required|numeric',
                'nombreChambre' => 'nullable|integer|min:1',
                'equipements' => 'required|string|max:255',
                'localite_id' => 'required|exists:localites,id',
                'image.*' => 'required|file',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 422);
            }

            $proprietaire = Proprietaire::where('user_id', Auth::user()->id)->first();

            $logements = new Logement();

            $logements->adresse = $request->input('adresse');
            $logements->type = $request->input('type');
            $logements->prix = $request->input('prix');
            $logements->nombreChambre = $request->input('nombreChambre');
            $logements->superficie = $request->input('superficie');
            $logements->disponibilite = $request->input('disponibilite');
            $logements->equipements = $request->input('equipements');
            $logements->description = $request->input('description');
            $logements->proprietaire_id = $proprietaire->id;
            $logements->localite_id = $request->input('localite_id');

            $logements->save();

            $imagesData = [];
            foreach ($request->file('image') as $file) {
                
                $images = new Image();
                $imagePath = $file->store('images/logement', 'public');
                $images->nomImage = $imagePath;
                $images->logement_id = $logements->id;
                $images->save();

                $imagesData[] = $images;
            }

            return response()->json([
                "message" => "Logement enregistré avec succès",
                "logement" => $logements,
                "images" => $imagesData,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }


    /**
     * Display the specified resource.
     */

    /**
     * @OA\Get(
     *      path="/api/detailLogement/{id}",
     *      operationId="getLogementById",
     *      tags={"Logements"},
     *      summary="Récupérer un logement par ID",
     *      description="Récupère les détails d'un logement en fonction de l'ID fourni.",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du logement à récupérer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer", format="int64"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Logement récupéré avec succès",
     *          @OA\JsonContent(
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
        $logements = Logement::where('id',  $id)->with(
            [
                'localite',
                'proprietaire',
                'images',
            ]
        )->get();
        return LogementDetailRessource::collection($logements);
    }




    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Logement $logement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * @OA\Post(
     *      path="/api/logements/{id}",
     *      operationId="updateLogement",
     *      tags={"Logements"},
     *      summary="Mettre à jour un logement",
     *      description="Met à jour les détails d'un logement en fonction de l'ID fourni.",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du logement à mettre à jour",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer", format="int64"),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="adresse", type="string"),
     *                  @OA\Property(property="type", type="string"),
     *                  @OA\Property(property="disponibilite", type="string", format="date"),
     *                  @OA\Property(property="description", type="string"),
     *                  @OA\Property(property="superficie", type="number"),
     *                  @OA\Property(property="prix", type="number"),
     *                  @OA\Property(property="nombreChambre", type="integer"),
     *                  @OA\Property(property="equipements", type="string"),
     *                  @OA\Property(property="localite_id", type="integer"),
     *                  @OA\Property(property="image[]", type="array", @OA\Items(type="string", format="binary")),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Logement mis à jour avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Logement mis à jour avec succès"),
     *     
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
     *          description="Erreur de validation",
     *          @OA\JsonContent(
     *              @OA\Property(property="errors", type="object", example={"adresse": {"Le champ adresse est obligatoire."}}),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Vous n'avez pas la permission de modifier ce logement",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Vous n'avez pas la permission de modifier ce logement."),
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
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $proprietaire = Proprietaire::where('user_id', $user->id)->first();

            // $logement = $proprietaire->logements()->find($id);
            $logement = Logement::where('proprietaire_id', $proprietaire->id)->find($id);


            if (!$logement) {
                return response()->json(['error' => 'Vous n\'avez pas la permission de modifier ce logement.'], 403);
            }


            $validate = Validator::make($request->all(), [
                'adresse' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'disponibilite' => 'required|date',
                'description' => 'required|string',
                'superficie' => 'required|numeric',
                'prix' => 'required|numeric',
                'nombreChambre' => 'required|integer|min:1',
                'equipements' => 'required|string|max:255',
                'localite_id' => 'required|exists:localites,id',
                'image.*' => 'nullable|file',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 422);
            }

            $logement->adresse = $request->input('adresse');
            $logement->type = $request->input('type');
            $logement->prix = $request->input('prix');
            $logement->nombreChambre = $request->input('nombreChambre');
            $logement->superficie = $request->input('superficie');
            $logement->disponibilite = $request->input('disponibilite');
            $logement->equipements = $request->input('equipements');
            $logement->description = $request->input('description');
            $logement->localite_id = $request->input('localite_id');
            $logement->update();

            if ($request->file('image')) {
                $images = $logement->images()->get();

                foreach ($images as $image) {
                    Storage::disk('public')->delete($image->nomImage);
                    // $images->delete();
                }

                foreach ($request->file('image') as $file) {
                    $image = new Image();
                    $imagePath = $file->store('images/logement', 'public');
                    $image->nomImage = $imagePath;
                    $image->logement_id = $logement->id;
                    $image->save();
                }
            }

            if ($logement->save()) {
                $logement->images;
                return response()->json([
                    "message" => "Logement mis à jour avec succès",
                    "logement" => $logement,
                    // "images" => $logement->images
                ], 200);
            } else {
                return response()->json(["message" => "La mise à jour du logement a échoué"]);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Logement non trouvé"], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }


    /**
     * Remove the specified resource from storage.
     */

    /**
     * @OA\Delete(
     *      path="/api/logements/{id}",
     *      operationId="deleteLogement",
     *      tags={"Logements"},
     *      summary="Supprimer un logement",
     *      description="Supprime un logement en fonction de l'ID fourni.",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du logement à supprimer",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer", format="int64"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Logement supprimé",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Logement supprimé"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Vous n'avez pas la permission de supprimer ce logement",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Vous n'avez pas la permission de supprimer ce logement."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Logement non trouvé",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Logement non trouvé"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */

    public function destroy($id)
    {
        $user = Auth::user();
        $proprietaire = Proprietaire::where('user_id', $user->id)->first();

        $logement = Logement::where('proprietaire_id', $proprietaire->id)->find($id);

        $images = $logement->images()->get();

        if (!$logement) {
            return response()->json(['error' => 'Vous n\'avez pas la permission de supprimer ce logement.'], 403);
        }
        foreach ($images as $image) {
            Storage::disk('public')->delete($image->nomImage);
        }
        if ($logement->delete()) {
            return response()->json([
                'message' => 'logement supprimée',
            ]);
        } else {
            return response()->json([
                'message' => 'logement non supprimée',
            ], 404);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/whatsapp.proprietaire/{id}",
     *      operationId="redirigerWhatsApp",
     *      tags={"WhatsApp"},
     *      summary="Rediriger vers WhatsApp",
     *      description="Redirige vers l'application WhatsApp avec le numéro de téléphone du propriétaire.",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID du propriétaire",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer", format="int64"),
     *      ),
     *      @OA\Response(
     *          response=302,
     *          description="Redirection vers WhatsApp",
     *          @OA\Header(header="Location", description="URL de redirection", @OA\Schema(type="string")),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Propriétaire non trouvé",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Propriétaire non trouvé"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur interne du serveur",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="Une erreur s'est produite"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */

    public function redirigerWhatsApp($id)
    {
        try {
            if (!is_numeric($id)) {
                throw new Exception('L\'ID doit être numérique.');
            }

            $proprietaire = User::findOrFail($id);

            $numeroOriginal = $proprietaire->telephone;
            $numeroWhatsApp = preg_replace('/[^0-9]/', '', $numeroOriginal);

            if (empty($numeroWhatsApp)) {
                throw new Exception("Numéro de téléphone non valide. Numéro original : $numeroOriginal, Numéro nettoyé : $numeroWhatsApp");
            }

            $urlWhatsApp = "https://api.whatsapp.com/send?phone=$numeroWhatsApp";

            return redirect()->to($urlWhatsApp);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('whatsapp.proprietaire');
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * @OA\Get(
     *      path="/api/logementParlocalite/{localite}",
     *      operationId="logementParLocalite",
     *      tags={"Logements"},
     *      summary="Obtenir les logements par localité",
     *      description="Renvoie les logements associés à une localité spécifiée.",
     *      @OA\Parameter(
     *          name="localite",
     *          description="Instance de la classe Localite.",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer", format="int64"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Logements trouvés",
     *          @OA\JsonContent(
     *              @OA\Property(property="statut", type="string", example="OK"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Localité non trouvée",
     *          @OA\JsonContent(
     *              @OA\Property(property="statut", type="string", example="Erreur"),
     *              @OA\Property(property="message", type="string", example="Localité non trouvée"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur interne du serveur",
     *          @OA\JsonContent(
     *              @OA\Property(property="statut", type="string", example="Erreur"),
     *              @OA\Property(property="message", type="string", example="Une erreur s'est produite"),
     *          ),
     *      ),
     *     
     * )
     */
    public function logementParLocalite(Localite $localite)
    {
        try {

            if ($localite) {
                $logements = $localite->logements;

                return response()->json([
                    'statut' => 'OK',
                    'logements' => $logements,
                ]);
            } else {
                return response()->json([
                    'statut' => 'Erreur',
                    'message' => 'Localité non trouvée',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'statut' => 'Erreur',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
