# Gestions Comptes

API Laravel pour la gestion des comptes bancaires.

## Fonctionnalités principales

-   Création, consultation, modification et suppression de comptes (soft delete)
-   Blocage et déblocage des comptes épargne
-   Recherche de comptes par numéro ou ID
-   Filtres par type, statut, recherche, tri et pagination
-   Authentification et sécurité via Laravel Sanctum
-   Documentation API Swagger (l5-swagger)
-   Notifications par email et SMS

## Paramètres de requête pour la liste des comptes

-   `page` : Numéro de page (défaut : 1)
-   `limit` : Nombre d'éléments par page (défaut : 10, max : 100)
-   `type` : Filtrer par type (`epargne`, `cheque`)
-   `statut` : Filtrer par statut (`actif`, `bloque`, `ferme`)
-   `search` : Recherche par titulaire ou numéro
-   `sort` : Tri (`dateCreation`, `solde`, `titulaire`)
-   `order` : Ordre (`asc`, `desc`)

## Recherche de comptes par numéro ou ID

L'API permet de récupérer les détails d'un compte bancaire en utilisant soit son numéro de compte, soit son identifiant unique (UUID).

- **Endpoint** : `GET /tinkin/v1/comptes/{numeroOuId}/details`
- **Paramètres** :
  - `numeroOuId` : Numéro de compte (ex: "C00123456") ou ID UUID du compte
- **Réponse** : Détails complets du compte avec informations du client
- **Gestion d'erreur** : Retourne 404 si le compte n'est pas trouvé

## Suppression de comptes (Soft Delete)

La suppression des comptes utilise un soft delete pour préserver l'intégrité des données :

- **Endpoint** : `DELETE /tinkin/v1/comptes/{id}`
- **Conditions** :
  - Le compte doit être actif (non bloqué)
  - Le solde du compte doit être nul
- **Comportement** :
  - Le compte est marqué comme supprimé (champ `deleted_at`)
  - Les comptes supprimés n'apparaissent plus dans les listes normales
  - Les données restent accessibles pour audit et restauration si nécessaire

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## Lancer le serveur

```bash
php artisan serve
```

## Tests

```bash
php artisan test
```

## Déploiement Render & Sécurité HTTPS

### Correction du Mixed Content Swagger

Pour garantir que la documentation Swagger fonctionne sans erreur de mixed content sur Render (ou tout hébergement HTTPS), le projet applique :

-   **Forçage du HTTPS pour les assets Swagger** : la vue `resources/views/vendor/l5-swagger/index.blade.php` génère les URLs d'assets (CSS, JS, favicon) en HTTPS, même si la configuration Laravel (`APP_URL`) est en HTTP.
-   **Redirection automatique HTTP → HTTPS** : dans `public/index.php`, une redirection force toute requête HTTP (détectée via le header `X-Forwarded-Proto`) vers HTTPS. Cela protège contre les accès non sécurisés, même derrière un proxy ou sur Render.

### Bonnes pratiques Render/Laravel

1. **Variables d'environnement**
    - Définir dans Render :
        - `APP_URL=https://<votre-domaine.onrender.com>`
        - `ASSET_URL=https://<votre-domaine.onrender.com>` (optionnel)
2. **Proxy de confiance**
    - Dans `App\Http\Middleware\TrustProxies`, laissez `$proxies = '*'` et `$headers = \Illuminate\Http\Request::HEADER_X_FORWARDED_ALL;` pour que Laravel détecte correctement le HTTPS derrière Render.
3. **Nettoyer les caches après déploiement**
    - Exécutez :
        ```bash
        php artisan config:clear
        php artisan config:cache
        php artisan route:clear
        php artisan view:clear
        php artisan l5-swagger:generate
        ```
4. **Vérification**
    - Accédez à `/api/documentation` en HTTPS et vérifiez que tous les assets Swagger sont bien chargés en HTTPS (plus d'erreur mixed content dans la console navigateur).

### Exemple de redirection dans `public/index.php`

```php
<?php
// ...
// Redirection automatique HTTP -> HTTPS (utile sur Render, inoffensif en local)
if (
	 isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
	 $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'http'
) {
	 header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
	 exit();
}
// ...
```

Avec ces mesures, la documentation Swagger et toute l'application sont accessibles uniquement en HTTPS, sans erreur de contenu mixte.

## Auteur

Abdourahamane TINKIN DJEERI
