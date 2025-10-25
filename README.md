# Gestions Comptes

API Laravel pour la gestion des comptes bancaires.

## Fonctionnalités principales

-   Création, consultation, modification et suppression de comptes
-   Filtres par type, statut, recherche, tri et pagination
-   Authentification et sécurité via Laravel Sanctum
-   Documentation API Swagger (l5-swagger)

## Paramètres de requête pour la liste des comptes

-   `page` : Numéro de page (défaut : 1)
-   `limit` : Nombre d'éléments par page (défaut : 10, max : 100)
-   `type` : Filtrer par type (`epargne`, `cheque`)
-   `statut` : Filtrer par statut (`actif`, `bloque`, `ferme`)
-   `search` : Recherche par titulaire ou numéro
-   `sort` : Tri (`dateCreation`, `solde`, `titulaire`)
-   `order` : Ordre (`asc`, `desc`)

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

## Auteur

Abdourahamane TINKIN DJEERI
