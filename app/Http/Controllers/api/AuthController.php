<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        
   
}
