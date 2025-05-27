#!/bin/sh

# Wait for database to be ready
echo "Waiting for database to be ready..."
until nc -z db 3306; do
  echo "Database is not ready yet, waiting..."
  sleep 2
done

echo "Database is ready!"

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Wait a bit more to ensure database is fully initialized
sleep 5

# Run Symfony migrations
echo "Running Symfony migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm 