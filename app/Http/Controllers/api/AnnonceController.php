<?php

namespace App\Http\Controllers\api;

use App\Models\Annonce;


use App\Models\Etudiant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreAnnonceRequest;
use App\Http\Requests\UpdateAnnonceRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AnnonceController extends Controller
{
    /**
     * Display a listing of the resource.
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
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'budget' => 'required|numeric',
            'caracteristiques' => 'required|string',
        ]);

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
    public function update(Request $request, Annonce $annonce)
    {
        try {
            $request->validate([
                'description' => 'required|string',
                'budget' => 'required|numeric',
                'caracteristiques' => 'required|string',
            ]);
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

    public function marquerPriseEnCharge($id)
{
    try {
        $user = Auth::user();
        $etudiant = Etudiant::where('user_id', $user->id)->first();
        $annonce = $etudiant->annonces()->findOrFail($id);
        // $annonce->pr

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


}
