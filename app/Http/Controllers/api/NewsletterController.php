<?php

namespace App\Http\Controllers\api;

use App\Models\Newsletter;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreNewsletterRequest;
use App\Http\Requests\UpdateNewsletterRequest;

class NewsletterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/newsletter",
     *     summary="Créer une nouvelle inscription à la newsletter",
     *     description="Créer une nouvelle inscription à la newsletter avec l'adresse e-mail fournie",
     *     operationId="createNewsletter",
     *     tags={"Newsletter"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email"},
     *                 @OA\Property(property="email", type="string", format="email", description="L'adresse e-mail pour l'inscription à la newsletter")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inscription à la newsletter créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Message de succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object", description="Détails de l'erreur de validation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Message d'erreur")
     *         )
     *     )
     * )
     *
     * Créer une nouvelle inscription à la newsletter.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function create(Request $request)
    {
        try {
            $validate = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255|unique:newsletters',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'error' => $validate->errors()
                ], 422);
            }
            $newsletter = new Newsletter();
            $newsletter->email = $request->email;
            if ($newsletter->save()) {
                return response()->json([
                    'message' => 'Vous etes enregistrer merci'
                ]);
            } else {
                return response()->json([
                    'message' => 'Mail non reçu'
                ]);
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNewsletterRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Newsletter $newsletter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Newsletter $newsletter)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNewsletterRequest $request, Newsletter $newsletter)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Newsletter $newsletter)
    {
        //
    }
}
