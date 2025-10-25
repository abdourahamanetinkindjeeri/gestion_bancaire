# --- Étape 1 : Builder ---
FROM php:8.3-fpm AS builder

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev npm \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www

# Copier uniquement les fichiers nécessaires pour l’installation
COPY composer.json composer.lock ./

# Installer les dépendances PHP sans dev
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copier uniquement les fichiers essentiels
COPY app/ app/
COPY routes/ routes/
COPY config/ config/
COPY public/ public/
COPY database/ database/
COPY resources/ resources/
COPY bootstrap/ bootstrap/
COPY artisan ./
COPY .env.example .env

# Publier la doc Swagger
RUN php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=swagger-ui --force \
 && php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=config --force \
 && php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=views --force \
 && php artisan l5-swagger:generate

# Builder les assets front
RUN npm install && npm run build \
 && rm -rf node_modules

# Donner les droits aux répertoires nécessaires
RUN mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# --- Étape 2 : Image finale ---
FROM php:8.3-fpm

# Installer uniquement les dépendances runtime
RUN apt-get update && apt-get install -y libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Copier uniquement ce qui est nécessaire depuis le builder
COPY --from=builder /var/www /var/www

# Variables d’environnement
ENV APP_ENV=production \
    APP_DEBUG=false \
    L5_SWAGGER_UI_CSS=https://gestions-comptes.onrender.com/docs/asset/swagger-ui.css \
    L5_SWAGGER_UI_BUNDLE_JS=https://gestions-comptes.onrender.com/docs/asset/swagger-ui-bundle.js \
    L5_SWAGGER_UI_STANDALONE_PRESET_JS=https://gestions-comptes.onrender.com/docs/asset/swagger-ui-standalone-preset.js

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache

# Exposer le port
EXPOSE 8000

# Commande de démarrage
CMD php artisan migrate --force \
 && php artisan config:cache \
 && php artisan route:cache \
 && php artisan serve --host=0.0.0.0 --port=8000
