# Étape 1️⃣ : Build des dépendances PHP
FROM composer:2.6 AS composer-build

WORKDIR /app

# Copier uniquement les fichiers nécessaires à Composer
COPY composer.json composer.lock ./

# Installer les dépendances PHP sans scripts post-install
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Étape 2️⃣ : Image finale
FROM php:8.3-fpm-alpine

# Installer les extensions nécessaires
RUN apk add --no-cache bash git libpq postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Créer un utilisateur non-root
RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

WORKDIR /var/www/html

# Copier le dossier vendor depuis la première étape
COPY --from=composer-build /app/vendor ./vendor

# Copier tout le code de l’application
COPY . .

# Créer les répertoires nécessaires et appliquer les permissions
RUN mkdir -p storage/framework/{cache,data,sessions,testing,views} \
    && mkdir -p storage/logs bootstrap/cache \
    && chown -R laravel:laravel /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Exposer le port
EXPOSE 8000

# Copier le script d'entrée
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Passer à l'utilisateur non-root
USER laravel

# Commande de démarrage
ENTRYPOINT ["docker-entrypoint.sh"]
