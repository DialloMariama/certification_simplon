<?php

namespace App\Http\Controllers\api;

use App\Models\Localite;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LocaliteController extends Controller
{
    
    public function index()
    {
        return response()->json([
            'message' => 'Liste de toutes les localités',
            'localite' => Localite::all()
        ]);
    }

    public function store(Request $request)
{
    $user = Auth::user();
    $request->validate([
        'nomLocalite' => 'required|string|max:255',
        'commune' => 'required|string|max:255',
    ]);
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

    public function update(Request $request, $id)
    {
        $localite = Localite::findOrFail($id);
        $request->validate([
            'nomLocalite' => 'required|string|max:255',
            'commune' => 'required|string|max:255',
        ]);
        $localite->nomLocalite = $request->input('nomLocalite');
        $localite->commune = $request->input('commune');

        if($localite->update()){
            return response()->json([
                'localite'=>$localite,
                'message'=> 'Localité modifiée',
            ]);
        }else{
            return response()->json([
                'message'=> 'Localité non modifiée',
            ],404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $localite = Localite::findOrFail($id);
        if($localite->delete()){
            return response()->json([
                'message'=> 'Localité supprimée',
            ]);
        }else{
            return response()->json([
                'message'=> 'Localité non supprimée',
            ],404);
        }
    }
}
