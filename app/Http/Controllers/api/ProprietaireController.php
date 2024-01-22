<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Localite;
use App\Models\Logement;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreProprietaireRequest;
use App\Http\Requests\UpdateProprietaireRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ProprietaireController extends Controller
{

    public function registerProprietaire(Request $request)
    {
        try {
            $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'adresse' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'telephone' => 'nullable|numeric|regex:/^[0-9]{9}$/',
                'role' => 'required|string|in:proprietaire',
            ]);

            $user = new User();
            $proprietaire = new Proprietaire();

            $user->nom = $request->input('nom');
            $user->prenom = $request->input('prenom');
            $user->email = $request->input('email');
            $user->telephone = $request->input('telephone');
            $user->role = $request->input('role');
            $user->adresse = $request->input('adresse');
            $user->password = Hash::make($request->password);

            $user->save();
            $proprietaire->user_id = $user->id;

            if ($proprietaire->save()) {
                return response()->json([
                    "message" => "Etudiant enregistré avec success",
                    "proprietaire" => array_merge(array($proprietaire), array($user))
                ]);
            } else {
                $user->delete();
                return response()->json(["message" => "L'inscription a échoué"]);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function updateProprietaire(Request $request)
{
    try {
        $user = Auth::user();

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telephone' => 'nullable|numeric|regex:/^[0-9]{9}$/',
        ]);

        $user = User::findOrFail($user->id);
        $proprietaire = Proprietaire::where('user_id', $user->id)->first();
        if (!$proprietaire) {
            return response()->json(['error' => 'Étudiant non trouvé'], 404);
        }

        $user->nom = $request->input('nom', $user->nom);
        $user->prenom = $request->input('prenom', $user->prenom);
        $user->email = $request->input('email', $user->email);
        $user->adresse = $request->input('adresse', $user->adresse);
        $user->telephone = $request->input('telephone', $user->telephone);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($user->save() && $proprietaire->save()) {
            return response()->json([
                "message" => 'Informations mises à jour avec succès',
            ]);
        } else {
            return response()->json(["message" => "La mise à jour a échoué"]);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function index()
{
    try {
        
        $user = Auth::user()->proprietaires()->first();
        // dd(Auth::user()->proprietaires()->get());
        // dd($user->id);

        $logements = Logement::with('images')->where('proprietaire_id', $user->id)->get();

        return response()->json([
            "logements" => $logements,
        ]);

    } catch (ModelNotFoundException $e) {
        return response()->json(["message" => "Logements non trouvés"], 404);
    } catch (\Exception $e) {
        return response()->json(["message" => "Une erreur s'est produite"], 500);
    }
}



    /**
     * Display a listing of the resource.
     */
    public function index1()
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
    // public function store(StoreProprietaireRequest $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    public function show(Proprietaire $proprietaire)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Proprietaire $proprietaire)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProprietaireRequest $request, Proprietaire $proprietaire)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Proprietaire $proprietaire)
    {
        //
    }
}
