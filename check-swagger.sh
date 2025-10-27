#!/bin/bash

# URL de la documentation Swagger
SWAGGER_URL="https://jeeri.onrender.com/api/documentation"

echo "🔍 Vérification de Swagger UI à $SWAGGER_URL..."

# Vérifie que la page est accessible
STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$SWAGGER_URL")

if [ "$STATUS" -eq 200 ]; then
  echo "✅ Swagger UI est accessible (HTTP 200)"
else
  echo "❌ Swagger UI inaccessible (HTTP $STATUS)"
  exit 1
fi

# Vérifie que les assets sont bien en HTTPS
echo "🔍 Vérification des assets Swagger..."

ASSETS=$(curl -s "$SWAGGER_URL" | grep -Eo 'src="http://[^"]+|href="http://[^"]+')

if [ -z "$ASSETS" ]; then
  echo "✅ Aucun asset Swagger en HTTP détecté"
else
  echo "❌ Assets Swagger en HTTP détectés :"
  echo "$ASSETS"
  exit 2
fi

echo "🎉 Swagger UI est sécurisé et prêt à l’emploi !"
