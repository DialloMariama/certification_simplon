<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Etudiant;
use App\Models\Logement;
use App\Models\Proprietaire;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *      name="Authentification",
 *     description="Points de terminaison API pour l'authentification ,l'inscription affichage des informations, la deconnexion et raffrechissement des tokens des utilisateurs"
 * )
 */
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password) || !$user->etat) {
            return response()->json(['message' => 'Vous n\'êtes pas autorisé à effectuer cette action car les informations d\'identification sont incorrectes ou le compte est désactivé'], 401);
        }

        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'user' => Auth::user(),
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
                'message' => 'Vous vous êtes connecté avec succès',
            ],
        ]);
    }

    public function userInformation()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'Vous vous êtes deconnectés',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }
    public function bloquerUser(User $user)
    {
        $statusMessage = $user->etat ? 'L\'utilisateur a été bloqué avec succès' : 'Le user est déjà bloqué.';
        $statusCode = $user->etat ? 200 : 400;

        $user->etat = false;

        if ($user->save()) {
            return response()->json([
                'status_code' => $statusCode,
                'status_message' => $statusMessage,
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors du bloquage du user',
            ]);
        }
    }
    public function debloquerUser(User $user)
    {
        $statusMessage = $user->etat ? 'Le user est déjà debloqué.' : 'L\'utilisateur a été debloqué avec succès';
        $statusCode = $user->etat ? 200 : 400;

        $user->etat = true;

        if ($user->save()) {
            return response()->json([
                'status_code' => $statusCode,
                'status_message' => $statusMessage,
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors du debloquage du user',
            ]);
        }
    }

    public function listeUtilisateursBloques()
    {
        $utilisateursBloques = User::where('etat', false)->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des utilisateurs bloqués récupérée avec succès',
            'data' => $utilisateursBloques,
        ]);
    }
    public function listeUtilisateurs()
    {
        $utilisateurs = User::where('etat', true)->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des utilisateurs actifs récupérée avec succès',
            'data' => $utilisateurs,
        ]);
    }

    public function listeEtudiantsNonBloques()
{
    $etudiantsNonBloques = Etudiant::with('user:id,nom,prenom,telephone,adresse,email,role')
        ->whereHas('user', function ($query) {
            $query->where('etat', true);
        })->get();

    return response()->json([
        'status_code' => 200,
        'status_message' => 'Liste des étudiants non bloqués récupérée avec succès',
        'data' => $etudiantsNonBloques,
    ]);
}

public function listeProprietairesNonBloques()
{
    $proprietairesNonBloques = Proprietaire::with('user:id,nom,prenom,telephone,adresse,email,role')
    ->whereHas('user', function ($query) {
        $query->where('etat', true);
    })->get();

    return response()->json([
        'status_code' => 200,
        'status_message' => 'Liste des étudiants non bloqués récupérée avec succès',
        'data' => $proprietairesNonBloques,
    ]);
}

public function index()
    {
        try {
            $logements = Logement::with('images')->get();
            return response()->json([
                "logements" => $logements,
            ]);
        } catch (\Exception $e) {
            return response()->json(["message" => "Une erreur s'est produite"], 500);
        }
    }

}
