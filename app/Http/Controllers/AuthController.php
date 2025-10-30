<?php

namespace App\Http\Controllers;


use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponser;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }


    // POST /api/v1/auth/login
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $result = $this->authService->login($credentials);
        if (!$result['success']) {
            return $this->error($result['message'] ?? 'Identifiants invalides', 401);
        }
        return $this->success($result['message'] ?? 'Connexion réussie', $result['data']);
    }


    // POST /api/v1/auth/refresh
    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');
        $result = $this->authService->refresh($refreshToken);
        if (!$result['success']) {
            return $this->error($result['message'] ?? 'Refresh token invalide', 401);
        }
        return $this->success($result['message'] ?? 'Token rafraîchi', $result['data']);
    }

    // POST /api/v1/auth/logout
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return $this->success('Déconnexion réussie');
    }
}
