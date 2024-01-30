<?php

namespace App\Http\Controllers\api;

use Exception;


use App\Models\User;
use App\Models\Image;
use App\Models\Logement;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Localite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
     *              @OA\Property(property="logement", type="array", @OA\Items(ref="#/components/schemas/LogementWithCommentairesAndImage")),
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
            $logement = Logement::with(['commentaires', 'images' => function ($query) {
                $query->first();
            }])->get();

            return response()->json([
                "logement" => $logement,
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
     *      path="/api/ajoutLogements",
     *      operationId="createLogement",
     *      tags={"Logements"},
     *      summary="Créer un nouveau logement",
     *      description="Crée un nouveau logement avec les détails fournis.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Données du logement",
     *          @OA\JsonContent(
     *              required={"adresse", "type", "disponibilite", "description", "superficie", "prix", "equipements", "localite_id", "image"},
     *              @OA\Property(property="adresse", type="string", description="Adresse du logement"),
     *              @OA\Property(property="type", type="string", description="Type de logement"),
     *              @OA\Property(property="disponibilite", type="date", description="Date de disponibilité du logement"),
     *              @OA\Property(property="description", type="string", description="Description du logement"),
     *              @OA\Property(property="superficie", type="numeric", description="Superficie du logement"),
     *              @OA\Property(property="prix", type="numeric", description="Prix du logement"),
     *              @OA\Property(property="nombreChambre", type="integer", description="Nombre de chambres du logement", minimum=1),
     *              @OA\Property(property="equipements", type="string", description="Équipements du logement"),
     *              @OA\Property(property="localite_id", type="integer", description="ID de la localité associée", example=1),
     *              @OA\Property(property="image", type="array", description="Tableau d'images du logement",
     *                  @OA\Items(type="file", format="binary")
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Logement enregistré avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Logement enregistré avec succès"),
     *              @OA\Property(property="logement", ref="#/components/schemas/Logement"),
     *              @OA\Property(property="images", type="array", @OA\Items(ref="#/components/schemas/Image")),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Erreur de validation",
     *          @OA\JsonContent(
     *              @OA\Property(property="errors", type="object", example={"adresse": {"Le champ adresse est obligatoire."}})
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * 
     * )
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
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
     *              @OA\Property(property="logement", ref="#/components/schemas/Logement"),
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
        try {
            $logements = Logement::with('images')->findOrFail($id);

            return response()->json([
                "logement" => $logements,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Logement non trouvé"], 404);
        } catch (\Exception $e) {
            return response()->json(["message" => "Une erreur s'est produite"], 500);
        }
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
     * @OA\Put(
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
     *              mediaType="application/json",
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
     *                  @OA\Property(property="remplacer_images", type="boolean"),
     *                  @OA\Property(property="image", type="array", @OA\Items(type="string", format="binary")),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Logement mis à jour avec succès",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Logement mis à jour avec succès"),
     *              @OA\Property(property="logement", ref="#/components/schemas/Logement"),
     *              @OA\Property(property="images", type="array", @OA\Items(ref="#/components/schemas/Image")),
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

            $logement = $proprietaire->logements()->find($id);

            if (!$logement) {
                return response()->json(['error' => 'Vous n\'avez pas la permission de modifier ce logement.'], 403);
            }

            if ($request->has('remplacer_images') && $request->remplacer_images) {
                $logement->images()->delete();
            }
            $request->validate([
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
                foreach ($request->file('image') as $file) {
                    $image = new Image();
                    $imagePath = $file->store('images/logement', 'public');
                    $image->nomImage = $imagePath;
                    $image->logement_id = $logement->id;
                    $image->save();
                }
            }

            if ($logement->save()) {
                return response()->json([
                    "message" => "Logement mis à jour avec succès",
                    "logement" => $logement,
                    "images" => $logement->images
                ]);
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
        $logement = $proprietaire->logements()->find($id);

        if (!$logement) {
            return response()->json(['error' => 'Vous n\'avez pas la permission de supprimer ce logement.'], 403);
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
 *      path="/api/logement-par-localite/{localite}",
 *      operationId="logementParLocalite",
 *      tags={"Logement"},
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
 *              @OA\Property(property="logements", type="array", @OA\Items(ref="#/components/schemas/Logement")),
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
 *      @OA\SecurityRequirement(
 *          bearerAuth={},
 *      ),
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
            // Capturez les exceptions pour éviter des erreurs non gérées.
            return response()->json([
                'statut' => 'Erreur',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
