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
    // public function destroy($id)
    // {
    //     $user = Auth::user();
    //     $etudiant = Etudiant::where('user_id', $user->id)->first();

    //     $commentaire = Commentaire::find($id);

    //     if (!$commentaire) {
    //         return response()->json(['error' => 'Le commentaire spécifié n\'existe pas.'], 404);
    //     }

    //     // Vérifiez si l'utilisateur est administrateur
    //     if ($user->hasRole('admin') || ($etudiant && $commentaire->etudiant_id === $etudiant->id)) {
    //         if ($commentaire->delete()) {
    //             return response()->json([
    //                 'message' => 'Commentaire supprimé',
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'message' => 'Une erreur s\'est produite lors de la suppression du commentaire',
    //             ], 500);
    //         }
    //     } else {
    //         return response()->json(['error' => 'Vous n\'avez pas la permission de supprimer ce commentaire.'], 403);
    //     }
    // }
}
