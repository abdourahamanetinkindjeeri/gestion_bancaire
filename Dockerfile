# -----------------------------
# Étape 1 : Builder PHP
# -----------------------------
FROM php:8.3-fpm AS builder

# Installer dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libzip-dev libpq-dev zip unzip git curl npm \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip opcache \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copier le code source
COPY . .

# Installer dépendances PHP (prod uniquement)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Publier les assets et config Swagger (forcés)
RUN php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=swagger-ui --force \
 && php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=config --force \
 && php artisan vendor:publish --provider="L5Swagger\\L5SwaggerServiceProvider" --tag=views --force

# Générer la documentation Swagger
RUN php artisan l5-swagger:generate || true

# Construire le front
RUN npm install && npm run build

# Préparer les dossiers Laravel
RUN mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache storage/api-docs \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Ne pas générer la clé ici ! (Elle sera générée au runtime)
# RUN php artisan key:generate --force

# -----------------------------
# Étape 2 : Runtime final
# -----------------------------
FROM php:8.3-fpm

# Copier PHP extensions et configs depuis builder
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Copier le code Laravel
WORKDIR /var/www
COPY --from=builder /var/www .

# Changer les permissions
RUN chown -R www-data:www-data /var/www \
 && chmod -R 775 storage bootstrap/cache

# Variables d’environnement
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=https://jeeri.onrender.com

# Exposer le port Laravel
EXPOSE 8000

# Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
