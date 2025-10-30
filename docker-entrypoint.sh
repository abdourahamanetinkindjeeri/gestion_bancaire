#!/bin/sh

set -e

echo "⏳ Waiting for database to be ready..."
while ! pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" > /dev/null 2>&1; do
  echo "Database is unavailable - sleeping..."
  sleep 2
done
echo "✅ Database is up!"

# Exécuter les migrations
echo "📦 Running migrations..."
php artisan migrate --force

# Générer les clés Passport uniquement si elles n'existent pas
if [ ! -f storage/oauth-private.key ]; then
  echo "🔐 Generating Passport keys..."
  php artisan passport:keys --force
else
  echo "🔑 Passport keys already exist, skipping generation."
fi

# Optimiser Laravel (cache config/routes/views)
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Lancer les tâches planifiées et le worker dans des processus séparés
echo "⏰ Starting scheduler..."
while true; do
  php artisan schedule:run --verbose --no-interaction &
  sleep 60
done &

echo "🚀 Starting queue worker..."
php artisan queue:work --verbose --tries=3 --timeout=90 &

# Démarrer l’application Laravel (web server)
echo "🌍 Starting Laravel app..."
exec php artisan serve --host=0.0.0.0 --port=8000
