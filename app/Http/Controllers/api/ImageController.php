<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;


use App\Models\Image;
use App\Models\Logement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreImageRequest;
use App\Http\Requests\UpdateImageRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function addImage(Request $request, $logementId)
    {
        try {
            $logement = Logement::findOrFail($logementId);
            $user = Auth::user();
    
            if ($user->proprietaire->id != $logement->proprietaire_id) {
                return response()->json(['error' => 'Vous n\'avez pas la permission d\'ajouter une image à ce logement.'], 403);
            }
    
            $request->validate([
                'image.*' => 'required|file',
            ]);
    
            $image = new Image();
            $imagePath = $request->file('image')->store('images/logement', 'public');
            $image->nomImage = $imagePath;
            $image->logement_id = $logement->id;
            $image->save();
            
    
            return response()->json([
                "message" => "Image ajoutée avec succès",
                "image" => $image,
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
            dd(Auth::user());
    
            $logement = Logement::where('id', $logementId)
                ->where('proprietaire_id', $user->proprietaire->id)
                ->firstOrFail();
    
            if ($request->has('remplacer_images') && $request->remplacer_images) {
                $logement->images()->delete();
            }
    
            $request->validate([
                'image.*' => 'nullable|file',
            ]);
    
            // Ajouter les nouvelles images
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
     * Remove the specified resource from storage.
     */
    public function deleteImage($logementId, $imageId)
    {
        try {
            $logement = Logement::findOrFail($logementId);
            $user = Auth::user();
    
            // Vérifiez si l'utilisateur est le propriétaire du logement
            if ($user->proprietaire->id != $logement->proprietaire_id) {
                return response()->json(['error' => 'Vous n\'avez pas la permission de supprimer cette image.'], 403);
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
