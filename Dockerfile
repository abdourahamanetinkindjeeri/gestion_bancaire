# --- Étape 1 : Builder ---
FROM php:8.3-fpm AS builder

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev npm \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

# Installer Composer (depuis l’image officielle)
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www

# Copier uniquement les fichiers nécessaires pour l’installation
COPY composer.json composer.lock ./

# Installer les dépendances PHP sans dev
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copier le reste du projet
COPY . .

# Publier les assets et générer la doc Swagger
RUN php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=swagger-ui --force \
 && php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=config --force \
 && php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=views --force

# Nettoyer le cache et générer Swagger
RUN php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear \
 && mkdir -p storage/api-docs \
 && chown -R www-data:www-data storage/api-docs \
 && chmod -R 775 storage/api-docs \
 && php artisan l5-swagger:generate

# Builder les assets front
RUN npm install && npm run build

# Donner les droits aux répertoires nécessaires
RUN chown -R www-data:www-data storage bootstrap/cache

# --- Étape 2 : Image finale (plus légère) ---
FROM php:8.3-fpm

# Installer les dépendances nécessaires au runtime
RUN apt-get update && apt-get install -y libpng-dev libonig-dev libxml2-dev libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copier le code depuis le builder
COPY --from=builder /var/www /var/www

# Définir le dossier de travail
WORKDIR /var/www

# Variables d’environnement
ENV APP_ENV=production \
    APP_DEBUG=false \
    L5_SWAGGER_UI_CSS=https://gestions-comptes.onrender.com/docs/asset/swagger-ui.css \
    L5_SWAGGER_UI_BUNDLE_JS=https://gestions-comptes.onrender.com/docs/asset/swagger-ui-bundle.js \
    L5_SWAGGER_UI_STANDALONE_PRESET_JS=https://gestions-comptes.onrender.com/docs/asset/swagger-ui-standalone-preset.js

# Exposer le port
EXPOSE 8000

# Commande de démarrage
CMD php artisan migrate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=8000
