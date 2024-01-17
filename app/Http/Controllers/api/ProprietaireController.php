<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreProprietaireRequest;
use App\Http\Requests\UpdateProprietaireRequest;
use App\Http\Controllers\Controller;


class ProprietaireController extends Controller
{

    public function registerProprietaire(Request $request)
    {
        // dd($request->all());
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
            $etudiant = new Proprietaire();

            $user->nom = $request->input('nom');
            $user->prenom = $request->input('prenom');
            $user->email = $request->input('email');
            $user->telephone = $request->input('telephone');
            $user->role = $request->input('role');
            $user->adresse = $request->input('adresse');
            $user->password = Hash::make($request->password);

            $user->save();
            $etudiant->user_id = $user->id;

            if ($etudiant->save()) {
                return response()->json([
                    "message" => "Etudiant enregistré avec success",
                    "etudiant" => array_merge(array($etudiant), array($user))
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
    public function store(StoreProprietaireRequest $request)
    {
        //
    }

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
