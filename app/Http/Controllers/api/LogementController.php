<?php

namespace App\Http\Controllers\api;

use App\Models\Image;


use App\Models\Localite;
use App\Models\Logement;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LogementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $logement = Logement::with(['images' => function ($query) {
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

    public function addImage(Request $request, $logementId)
    {
        try {
            $logement = Logement::findOrFail($logementId);
            $user = Auth::user()->proprietaires()->first();
    
      
            if ($user->id != $logement->proprietaire_id) {
                return response()->json(['error' => 'Vous n\'avez pas la permission d\'ajouter une image à ce logement.'], 403);
            }
        
            $request->validate([
                'image.*' => 'required|file',
            ]);
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
                "image" =>$imagesData,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(["message" => "Logement non trouvé"], 404);
        } catch (\Exception $e) {
            return response()->json(["message" => "Une erreur s'est produite"], 500);
        }
    }

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
