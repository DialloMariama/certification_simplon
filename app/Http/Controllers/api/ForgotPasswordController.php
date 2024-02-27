<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forget password form.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * Submit the forget password form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitForgetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ],
        [
            'email.required' => 'Le champ email est requis.',
            'email.email' => 'Veuillez entrer une adresse email valide.',
            'email.exists' => 'Cet email n\'est pas enregistré dans notre système.',
        ]);

        $token = Str::random(64);
        $expiry = Carbon::now()->addMinutes(5);

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'expires_at' => $expiry,
            'created_at' => Carbon::now(),
        ]);

        Mail::send('forgetPassword', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        return response()->json(['message' => 'Nous vous avons envoyé un email de récupération!']);
    }

    /**
     * Show the reset password form.
     *
     * @param  string  $token
 
     */
    public function showResetPasswordForm($token)
    {
        return view('resetPassword', ['token' => $token]);
    }

    public function showResetPasswordSuccess()
{
    return view('successResetPassword');
}


    /**
     * Submit the reset password form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitResetPasswordForm(Request $request)
    {
        $request->validate([
            'password' => 'required|regex:/^(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[@#$%^&+=!])(.{8,})$/',
            'password_confirmation' => 'required|regex:/^(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[@#$%^&+=!])(.{8,})$/',
        ],
        [
            'password.required' => 'Le champ mot de passe est requis.',
            'password.regex' => 'Le mot de passe doit contenir au moins 8 caractères, avec au moins une lettre, un chiffre et un caractère spécial (@#$%^&+=!).',
            'password_confirmation.required' => 'Le champ confirmation du mot de passe est requis.',
            'password_confirmation.same' => 'Le champ confirmation du mot de passe doit correspondre au mot de passe.',
        ]);

        $tokenData = DB::table('password_reset_tokens')
            ->where('token',$request->token)
            ->first();

        if (!$tokenData) {
            return response()->json(['error' => 'données invalides!'], 422);
        }

        if (now()->gt($tokenData->expires_at)) {
            DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->delete();
            return response()->json(['error' => 'Le token de réinitialisation de mot de passe a expiré!'], 422);
        }

        DB::table('users')
        ->where('email', $tokenData->email)
        ->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')
        ->where('token', $request->token)
        ->delete();

        // return response()->json(['message' => 'Votre mot de passe a été mis a jour'],204);
        return redirect()->route('password.reset.success');
    }
}
