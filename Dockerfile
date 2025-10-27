# -----------------------------
# Stage 1: Builder
# -----------------------------
FROM php:8.3-fpm AS builder

# Installer dépendances système et extensions PHP nécessaires pour composer, build et Swagger
RUN apt-get update && apt-get install -y \
        git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev npm \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip opcache \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www

# Copier les fichiers source
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Publier et générer la doc Swagger
RUN php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider" --force \
    && php artisan l5-swagger:generate

# Installer les dépendances JS et builder le front
RUN npm install && npm run build

# Nettoyer caches Laravel
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Télécharger et copier les assets Swagger UI (version 5.11.0)
RUN curl -L -o /tmp/swagger-ui.zip https://github.com/swagger-api/swagger-ui/archive/refs/tags/v5.11.0.zip \
    && unzip /tmp/swagger-ui.zip -d /tmp \
    && mkdir -p public/vendor/swagger-api/swagger-ui/dist \
    && cp -r /tmp/swagger-ui-5.11.0/dist/* public/vendor/swagger-api/swagger-ui/dist/ \
    && rm -rf /tmp/swagger-ui*

# -----------------------------
# Stage 2: Runtime
# -----------------------------
FROM php:8.3-fpm

WORKDIR /var/www

# Installer extensions PHP runtime
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copier l’application construite depuis le builder
COPY --from=builder /var/www /var/www

# Copier le script d’entrée
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Préparer storage et bootstrap/cache avec bonnes permissions
RUN mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache storage/api-docs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Variables d'environnement (peuvent aussi être définies via Render)
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=https://jeeri.onrender.com

# Exposer le port Laravel
EXPOSE 8000

# Entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
