#!/bin/sh

# --------------------------------------------------------------------
# 1️⃣ Attendre que la base de données soit prête
# --------------------------------------------------------------------
echo "Waiting for database to be ready..."
while ! pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; do
  echo "Database is unavailable - sleeping"
  sleep 1
done

echo "Database is up - executing migrations"
php artisan migrate --force

# --------------------------------------------------------------------
# 2️⃣ (Optionnel) Génération des clés
# --------------------------------------------------------------------
# ⚠️ À exécuter seulement lors du premier déploiement, pas à chaque start
php artisan key:generate --force
php artisan passport:install --force

# --------------------------------------------------------------------
# 3️⃣ Optimisations Laravel
# --------------------------------------------------------------------
echo "⚙️ Optimizing Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# --------------------------------------------------------------------
# 4️⃣ Lancer le scheduler en arrière-plan
# --------------------------------------------------------------------
echo "⏰ Starting Laravel scheduler..."
(
  while true; do
    php artisan schedule:run --verbose --no-interaction >> /dev/null 2>&1
    sleep 60
  done
) &

# --------------------------------------------------------------------
# 5️⃣ Démarrer l’application
# --------------------------------------------------------------------
echo "🚀 Starting Laravel application..."
exec "$@"
