<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangerMotDePasseRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RequestOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use OpenApi\Annotations as OA;

/**
 * Class AuthController
 * @package App\Http\Controllers\Api
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for User Authentication"
 * )
 * @OA\Schema(
 *     schema="AuthUser",
 *     title="Authenticated User",
 *     description="User object returned upon successful authentication",
 *     @OA\Property(property="id", type="string", format="uuid", description="User ID"),
 *     @OA\Property(property="nom_utilisateur", type="string", description="Username"),
 *     @OA\Property(property="type", type="string", description="User type (e.g., ADMIN, ECOLE)"),
 *     @OA\Property(property="telephone", type="string", nullable=true, description="User's phone number"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, description="User's email address"),
 *     @OA\Property(property="doit_changer_mot_de_passe", type="boolean", description="Indicates if user must change password"),
 *     @OA\Property(property="mot_de_passe_change", type="boolean", description="Indicates if user has changed password at least once")
 * )
 */
class AuthController extends Controller implements HasMiddleware
{
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', only: ['logout', 'me', 'changerMotDePasse']),
        ];
    }

    /**
     * Demander un code OTP pour connexion
     * @OA\Post(
     *     path="/api/auth/request-otp",
     *     summary="Request OTP for login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RequestOtpRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Code OTP envoyé avec succès."),
     *             @OA\Property(property="expires_in", type="string", example="5 minutes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"telephone": {"Aucun compte associé à ce numéro de téléphone."}})
     *         )
     *     )
     * )
     */
    public function requestOtp(RequestOtpRequest $request): JsonResponse
    {
        return $this->authService->requestOtp($request->telephone);
    }

    /**
     * Vérifier l'OTP et se connecter
     * @OA\Post(
     *     path="/api/auth/verify-otp",
     *     summary="Verify OTP and log in",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/VerifyOtpRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Connexion réussie."),
     *             @OA\Property(property="access_token", type="string", description="Bearer token"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="user", ref="#/components/schemas/AuthUser")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"otp": {"Code OTP invalide ou expiré."}})
     *         )
     *     )
     * )
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        return $this->authService->verifyOtpAndLogin($request->telephone, $request->otp);
    }

    /**
     * Connexion classique avec identifiant et mot de passe
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Log in with identifier and password",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Connexion réussie."),
     *             @OA\Property(property="access_token", type="string", description="Bearer token"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="user", ref="#/components/schemas/AuthUser")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"identifiant": {"Identifiants incorrects."}})
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->authService->login($request->identifiant, $request->mot_de_passe);
    }

    /**
     * Déconnexion
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Log out the authenticated user",
     *     tags={"Authentication"},
     *     security={ {"passport": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        //Gate::authorize('voir_son_propre_profil');
        return $this->authService->logout($request->user());
    }

    /**
     * Obtenir les informations de l'utilisateur connecté
     * @OA\Get(
     *     path="/api/auth/me",
     *     summary="Get authenticated user details",
     *     tags={"Authentication"},
     *     security={ {"passport": {}} },
     *     @OA\Response(
     *         response=200,
     *         description="User details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", ref="#/components/schemas/AuthUser")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        //Gate::authorize('voir_son_propre_profil');
        return $this->authService->me($request->user());
    }

    /**
     * Changer le mot de passe de l'utilisateur connecté
     * @OA\Post(
     *     path="/api/auth/change-password",
     *     summary="Change authenticated user's password",
     *     tags={"Authentication"},
     *     security={ {"passport": {}} },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ChangerMotDePasseRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Mot de passe changé avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"ancien_mot_de_passe": {"L'ancien mot de passe est incorrect."}})
     *         )
     *     )
     * )
     */
    public function changerMotDePasse(ChangerMotDePasseRequest $request): JsonResponse
    {
        Gate::authorize('changer_son_propre_mot_de_passe');
        return $this->authService->changerMotDePasse(
            $request->user(),
            $request->nouveau_mot_de_passe,
            $request->ancien_mot_de_passe
        );
    }
}
