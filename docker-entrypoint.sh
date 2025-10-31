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

# Générer les clés OAuth si elles n'existent pas
if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
  echo "🔐 Generating OAuth keys..."
  php artisan passport:keys --force
fi

php artisan migrate --force

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
