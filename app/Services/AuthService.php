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
     * Authentifie l'utilisateur et retourne les tokens avec scopes personnalisés.
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return ['success' => false, 'message' => 'Identifiants invalides'];
        }

        // Déterminer les scopes et claims personnalisés selon le rôle
        $scopes = [];
        $customClaims = [
            'user_id' => $user->id,
            'email' => $user->email,
            'telephone' => $user->telephone,
        ];

        if ($user->admin) {
            $scopes = ['admin:read', 'admin:write', 'admin:delete', 'client:read', 'compte:read', 'compte:write', 'compte:delete', 'transaction:read', 'transaction:write'];
            $customClaims['role'] = 'admin';
            $customClaims['admin_id'] = $user->admin->id;
        } elseif ($user->client) {
            $scopes = ['client:read', 'client:write', 'compte:read', 'transaction:read'];
            $customClaims['role'] = 'client';
            $customClaims['client_id'] = $user->client->id;
        } else {
            $customClaims['role'] = 'user';
        }

        // Créer le token avec scopes et claims personnalisés
        $tokenResult = $user->createToken('AccessToken', $scopes);
        $tokenResult->token->with($customClaims);

        $refreshToken = $user->createToken('RefreshToken');

        return [
            'success' => true,
            'data' => [
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'role' => $customClaims['role'],
                'scopes' => $scopes,
            ],
            'refresh_token' => $refreshToken->accessToken, // Retourner séparément pour le cookie
            'message' => 'Connexion réussie'
        ];
    }

    /**
     * Rafraîchit le token d'accès à partir du refresh token avec scopes et claims.
     */
    public function refresh(string $refreshToken): array
    {
        // Trouver le token de rafraîchissement dans la base de données
        $refreshTokenModel = \Laravel\Passport\RefreshToken::where('id', $refreshToken)->first();

        if (!$refreshTokenModel || $refreshTokenModel->revoked) {
            return ['success' => false, 'message' => 'Refresh token invalide'];
        }

        $accessToken = $refreshTokenModel->accessToken;

        if (!$accessToken || $accessToken->revoked) {
            return ['success' => false, 'message' => 'Access token associé invalide'];
        }

        $user = $accessToken->user;

        // Récupérer les scopes et claims du token original
        $originalScopes = $accessToken->scopes ?? [];
        $originalClaims = json_decode($accessToken->name ?? '{}', true) ?: [];

        // Créer un nouveau token avec les mêmes scopes
        $newTokenResult = $user->createToken('AccessToken', $originalScopes);

        // Ajouter les claims personnalisés
        $newToken = $newTokenResult->token;
        $newToken->name = json_encode($originalClaims);
        $newToken->save();

        // Révoquer l'ancien token et refresh token
        $accessToken->revoke();
        $refreshTokenModel->revoke();

        // Créer un nouveau refresh token
        $newRefreshToken = $user->createToken('RefreshToken');

        return [
            'success' => true,
            'data' => [
                'access_token' => $newTokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'role' => $originalClaims['role'] ?? null,
                'scopes' => $originalScopes,
            ],
            'refresh_token' => $newRefreshToken->accessToken,
            'message' => 'Token rafraîchi'
        ];
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
