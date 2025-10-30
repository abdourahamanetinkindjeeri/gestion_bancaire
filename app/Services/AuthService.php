<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;

class AuthService
{
    /**
     * Authentifie l'utilisateur et retourne les tokens.
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return ['success' => false, 'message' => 'Identifiants invalides'];
        }
        $tokenResult = $user->createToken('AccessToken');
        $refreshToken = $user->createToken('RefreshToken');

        // Déterminer le rôle de l'utilisateur
        $role = null;
        if ($user->admin) {
            $role = 'admin';
        } elseif ($user->client) {
            $role = 'client';
        }

        return [
            'success' => true,
            'data' => [
                'access_token' => $tokenResult->accessToken,
                'refresh_token' => $refreshToken->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'role' => $role,
            ],
            'message' => 'Connexion réussie'
        ];
    }

    /**
     * Rafraîchit le token d'accès à partir du refresh token.
     */
    public function refresh(string $refreshToken): array
    {
        // Pour Passport, le refresh token n'est pas directement utilisé comme ça.
        // Cette méthode est un placeholder. Pour une implémentation complète,
        // il faudrait utiliser Passport's refresh token flow.
        return ['success' => false, 'message' => 'Refresh token invalide'];
    }

    /**
     * Révoque les tokens de l'utilisateur (logout).
     */
    public function logout(User $user): void
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        $tokenRepository->revokeAccessToken($user->token()->id);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($user->token()->id);
    }
}
