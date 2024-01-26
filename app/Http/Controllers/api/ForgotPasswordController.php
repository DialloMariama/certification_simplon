<?php

namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
/**
 * @OA\Tag(
 *      name="Mot de passe oublié",
 *     description="Points de terminaison API pour la gestion Mot de passe oublié "
 * )
 */
class ForgotPasswordController extends Controller
{

    /**
 * @OA\Post(
 *      path="/api/forget-password",
 *      operationId="submitForgetPasswordForm",
 *      tags={"Mot de passe oublié"},
 *      summary="Soumettre le mail de récupération de mot de passe",
 *      description="Soumet le formulaire de récupération de mot de passe en envoyant un email avec un lien de réinitialisation.",
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Email de récupération envoyé avec succès",
 *          @OA\JsonContent(
 *              @OA\Property(property="message", type="string", example="Nous vous avons envoyé un email de récupération!"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=422,
 *          description="Données invalides",
 *          @OA\JsonContent(
 *              @OA\Property(property="error", type="string", example="Données invalides!"),
 *          ),
 *      ),
 * )
 */
    public function submitForgetPasswordForm(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
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
        return view('resetPassword', ['token' => $token]);    }

    /**
     * Submit the reset password form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitResetPasswordForm(Request $request)
    {
        $request->validate([
            'password' => 'required|regex:/^(?=.[0-9])(?=.[a-zA-Z])(?=.[@#$%^&+=!])(.{8,})$/',
            'password_confirmation' => 'required|regex:/^(?=.[0-9])(?=.[a-zA-Z])(?=.[@#$%^&+=!])(.{8,})$/',
        ]);

        $updatePassword = DB::table('password_reset_tokens')
            ->where([

                'token' => $request->token,
            ])
            ->first();

        if (!$updatePassword) {
            return response()->json(['error' => 'données invalides!'], 422);
        }

        $user = DB::table('password_reset_tokens')->where(['token' => $request->token])->first();

        User::where('email', $user->email)
            ->update(['password' => Hash::make($request->password)]);
            DB::table('password_reset_tokens')->where(['token'=> $request->token])->delete();
        return response()->json(['message' => 'Votre mot de passe a été mis a jour']);
    }
}
