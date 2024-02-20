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
 *     description="Points de terminaison API pour l'authentification ,affichage des informations, la deconnexion et raffrechissement des tokens des utilisateurs, bloquer et debloquer etudiant et proprietaire"
 * )
 */
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentification"},
     *     summary="Connecter un utilisateur existant",
     *     description="Connectez-vous avec un e-mail et un mot de passe pour obtenir un jeton d'authentification",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="email", type="string", example="utilisateur@example.com"),
     *                 @OA\Property(property="password", type="string", example="motdepasse"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object", description="Détails de l'utilisateur"),
     *             @OA\Property(property="authorization", type="object", description="Détails d'autorisation"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *     ),
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password) || !$user->etat || $user->inscriptionValidee !== 'valider') {
            return response()->json(['message' => 'Votre compte est désactivé ou votre inscription est en attente de validation'], 401);
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
                'expires_in' => Auth::factory()->getTTL() * 60
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/user",
     *     summary="Afficher les informations de l'utilisateur connecté",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Détails de l'utilisateur connecté",
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     ),
     *      security={{"bearerAuth": {}}},
     * )
     */
    public function userInformation()
    {
        return response()->json(auth()->user());
    }


    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Déconnexion d'un utilisateur",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     * security={{"bearerAuth": {}}},
     * )
     */
    public function logout()
    {
        Auth::logout();
        return response()->json([
            'message' => 'Vous vous êtes deconnectés',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Actualiser le jeton d'authentification",
     *     tags={"Authentification"},
     *     @OA\Response(
     *         response=200,
     *         description="Jeton d'authentification actualisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */
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


    /**
     * @OA\Post(
     *      path="/api/bloquerUser/{user}",
     *      operationId="bloquerUser",
     *      tags={"Authentification"},
     *      summary="Bloquer un utilisateur",
     *      description="Bloque un utilisateur dans le système.",
     *      @OA\Parameter(
     *          name="user",
     *          in="path",
     *          required=true,
     *          description="ID de l'utilisateur à bloquer",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="L'utilisateur a été bloqué avec succès.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status_code", type="integer", example=200),
     *              @OA\Property(property="status_message", type="string", example="L'utilisateur a été bloqué avec succès"),
     *              @OA\Property(property="data", type="object", example={}),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Le user est déjà bloqué.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status_code", type="integer", example=400),
     *              @OA\Property(property="status_message", type="string", example="Le user est déjà bloqué."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur lors du bloquage du user.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status_code", type="integer", example=500),
     *              @OA\Property(property="status_message", type="string", example="Erreur lors du bloquage du user."),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/debloquerUser/{user}",
     *      operationId="debloquerUser",
     *      tags={"Authentification"},
     *      summary="Débloquer un utilisateur",
     *      description="Débloque un utilisateur dans le système.",
     *      @OA\Parameter(
     *          name="user",
     *          in="path",
     *          required=true,
     *          description="ID de l'utilisateur à débloquer",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="L'utilisateur a été débloqué avec succès.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status_code", type="integer", example=200),
     *              @OA\Property(property="status_message", type="string", example="L'utilisateur a été débloqué avec succès."),
     *              @OA\Property(property="data", type="object", example={}),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Le user est déjà débloqué.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status_code", type="integer", example=400),
     *              @OA\Property(property="status_message", type="string", example="Le user est déjà débloqué."),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur lors du débloquage du user.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status_code", type="integer", example=500),
     *              @OA\Property(property="status_message", type="string", example="Erreur lors du débloquage du user."),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * )
     */
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


    /**
     * @OA\Get(
     *      path="/api/utilisateursBloques",
     *      operationId="listeUtilisateursBloques",
     *      tags={"Authentification"},
     *      summary="Liste des utilisateurs bloqués",
     *      description="Récupère la liste des utilisateurs bloqués dans le système.",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des utilisateurs bloqués récupérée avec succès.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status_code", type="integer", example=200),
     *              @OA\Property(property="status_message", type="string", example="Liste des utilisateurs bloqués récupérée avec succès."),
     *              @OA\Property(property="data", type="array", @OA\Items(type="object", example={})),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * )
     */
    public function listeUtilisateursBloques()
    {
        $utilisateursBloques = User::where('etat', false)->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des utilisateurs bloqués récupérée avec succès',
            'data' => $utilisateursBloques,
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/utilisateurs",
     *      operationId="listeUtilisateurs",
     *      tags={"Authentification"},
     *      summary="Liste des utilisateurs actifs",
     *      description="Récupère la liste des utilisateurs actifs dans le système.",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des utilisateurs actifs récupérée avec succès.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status_code", type="integer", example=200),
     *              @OA\Property(property="status_message", type="string", example="Liste des utilisateurs actifs récupérée avec succès."),
     *              @OA\Property(property="data", type="array", @OA\Items(type="object", example={})),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * )
     */
    public function listeUtilisateurs()
    {
        $utilisateurs = User::where('etat', true)->get();

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Liste des utilisateurs actifs récupérée avec succès',
            'data' => $utilisateurs,
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/etudiants",
     *      operationId="listeEtudiantsNonBloques",
     *      tags={"Authentification"},
     *      summary="Liste des étudiants non bloqués",
     *      description="Récupère la liste des étudiants non bloqués dans le système.",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des étudiants non bloqués récupérée avec succès.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status_code", type="integer", example=200),
     *              @OA\Property(property="status_message", type="string", example="Liste des étudiants non bloqués récupérée avec succès."),
     *              @OA\Property(property="data", type="array", @OA\Items(type="object", example={})),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * )
     */
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



    /**
     * @OA\Get(
     *      path="/api/proprietaires",
     *      operationId="listeProprietairesNonBloques",
     *      tags={"Authentification"},
     *      summary="Liste des propriétaires non bloqués",
     *      description="Récupère la liste des propriétaires non bloqués dans le système.",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des propriétaires non bloqués récupérée avec succès.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status_code", type="integer", example=200),
     *              @OA\Property(property="status_message", type="string", example="Liste des propriétaires non bloqués récupérée avec succès."),
     *              @OA\Property(property="data", type="array", @OA\Items(type="object", example={})),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * )
     */
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


    /**
     * @OA\Get(
     *      path="/api/logementsAdmin",
     *      operationId="indexAdmin",
     *      tags={"Authentification"},
     *      summary="Liste des logements pour un admin",
     *      description="Récupère la liste des logements avec leurs images associées pour un admin.",
     *      @OA\Response(
     *          response=200,
     *          description="Liste des logements récupérée avec succès.",
     *          @OA\JsonContent(
     *          
     *          ),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Une erreur s'est produite.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Une erreur s'est produite"),
     *          ),
     *      ),
     *      security={{"bearerAuth": {}}},
     * )
     */
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


    public function validerInscription($userId)
    {
        $user = User::findOrFail($userId);

        if ($user && !$user->inscription_validee) {
            $user->inscriptionValidee = 'valider';
            $user->save();

            return response()->json(['message' => 'Inscription validée avec succès.'], 200);
        }

        return response()->json(['message' => 'L\'utilisateur n\'existe pas ou son inscription est déjà validée.'], 404);
    }
    public function rejeterInscription($userId)
    {
        $user = User::findOrFail($userId);

        if ($user && !$user->inscription_validee) {
            $user->inscriptionValidee = 'rejeter';
            $user->save();

            return response()->json(['message' => 'Inscription rejetée avec succès.'], 200);
        }

        return response()->json(['message' => 'L\'utilisateur n\'existe pas ou son inscription est déjà rejetée.'], 404);
    }
}
