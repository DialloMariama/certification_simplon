<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Etudiant;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class EtudiantController extends Controller
{

    public function registerEtudiant(Request $request)
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
                'paysOrigine' => 'required|string|max:50',
                'universite' => 'required|string|max:100',
                'role' => 'required|string|in:etudiant',
            ]);

            $user = new User();
            $etudiant = new Etudiant();

            $user->nom = $request->input('nom');
            $user->prenom = $request->input('prenom');
            $user->email = $request->input('email');
            $user->telephone = $request->input('telephone');
            $user->role = $request->input('role');
            $user->adresse = $request->input('adresse');
            $user->password = Hash::make($request->password);

            $user->save();

            $etudiant->paysOrigine  = $request->input('paysOrigine');
            $etudiant->universite  = $request->input('universite');
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
   

    /**
     * Display the specified resource.
     */
    public function show(Etudiant $etudiant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Etudiant $etudiant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
  

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Etudiant $etudiant)
    {
        //
    }
}
