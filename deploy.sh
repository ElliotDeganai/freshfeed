#!/bin/bash
set -e

echo "🔄 Déploiement de CalTracker..."

cd /var/www/caltracker

echo "→ Passage de la propriété à ubuntu..."
sudo chown -R ubuntu:ubuntu /var/www/caltracker

echo "→ Récupération des derniers changements..."
git pull origin main

echo "→ Installation des dépendances PHP..."
composer install --optimize-autoloader --no-dev

echo "→ Installation des dépendances JS..."
npm install

echo "→ Build des assets front..."
npm run build

echo "→ Application des migrations..."
php artisan migrate --force

echo "→ Nettoyage des caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "→ Passage de la propriété à www-data..."
sudo chown -R www-data:www-data /var/www/caltracker
sudo chmod -R 755 /var/www/caltracker/storage /var/www/caltracker/bootstrap/cache

echo "→ Reconstruction des caches de production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Déploiement terminé !"
