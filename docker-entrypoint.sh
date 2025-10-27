#!/bin/sh

set -e  # Stop on first error

# Vérifie que .env existe
if [ ! -f .env ]; then
  echo "⚠️ Aucun fichier .env trouvé. Laravel risque de planter."
fi

# Assurer les bonnes permissions sur storage et bootstrap/cache
echo "🔧 Vérification des permissions..."
mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache storage/api-docs
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Générer la clé Laravel si non définie
if [ -z "$(php artisan key:generate --show 2>/dev/null)" ]; then
    echo "🔑 Génération de la clé Laravel..."
    php artisan key:generate --force
else
    echo "🔑 Clé Laravel déjà définie."
fi

# Nettoyer et préparer caches
echo "⚙️ Nettoyage et cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Exécuter migrations en production
echo "📦 Exécution des migrations..."
php artisan migrate --force

# Générer Swagger si nécessaire
if [ -d resources/views/vendor/l5-swagger ]; then
    echo "📄 Génération de la documentation Swagger..."
    php artisan l5-swagger:generate
fi

# Lancer le serveur Laravel
echo "🚀 Lancement du serveur Laravel..."
exec php artisan serve --host=0.0.0.0 --port=8000
