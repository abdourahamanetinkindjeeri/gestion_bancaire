#!/bin/sh
set -e  # Arrête le script en cas d'erreur

echo "🚀 Initialisation du conteneur Laravel..."

# Vérifie que .env existe
if [ ! -f .env ]; then
  echo "⚠️ Aucun fichier .env trouvé. Laravel risque de planter."
fi

# Création des dossiers nécessaires
echo "🔧 Vérification des permissions..."
mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache storage/api-docs
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Générer la clé Laravel si non définie
if ! php artisan key:generate --show >/dev/null 2>&1; then
    echo "🔑 Génération de la clé Laravel..."
    php artisan key:generate --force
else
    echo "🔑 Clé Laravel déjà définie."
fi

# Nettoyer et mettre en cache
echo "⚙️ Nettoyage et optimisation..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan optimize || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Exécution des migrations
echo "📦 Exécution des migrations..."
php artisan migrate --force || true

# Génération Swagger si le package est installé
if php artisan | grep -q l5-swagger; then
    echo "📄 Génération de la documentation Swagger..."
    php artisan l5-swagger:generate || true
else
    echo "⚠️ Swagger non installé, étape ignorée."
fi

# Définir le port (Render fournit PORT automatiquement)
PORT_TO_USE=${PORT:-8000}

echo "✅ Préparation terminée !"
echo "🌐 Lancement du serveur PHP-FPM sur le port $PORT_TO_USE..."

# Lancer PHP-FPM (production)
exec php-fpm
