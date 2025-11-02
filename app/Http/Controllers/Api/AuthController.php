<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RequestOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Demander un code OTP pour connexion
     */
    public function requestOtp(RequestOtpRequest $request)
    {
        try {
            $result = $this->authService->requestOtp($request->telephone);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Vérifier l'OTP et se connecter
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        try {
            $result = $this->authService->verifyOtpAndLogin($request->telephone, $request->otp);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Connexion classique avec identifiant et mot de passe
     */
    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->identifiant, $request->mot_de_passe);

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        try {
            $result = $this->authService->logout($request->user());

            return response()->json($result, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir les informations de l'utilisateur connecté
     */
    public function me(Request $request)
    {
        $user = $request->user()->load(['userInfo', 'role.permissions']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'nom_utilisateur' => $user->nom_utilisateur,
                'identifiant' => $user->identifiant,
                'type' => $user->type,
                'telephone' => $user->userInfo->telephone ?? null,
                'email' => $user->userInfo->email ?? null,
                'role' => $user->role,
                'permissions' => $user->role->permissions ?? [],
            ],
        ], 200);
    }
}
