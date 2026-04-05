#!/bin/bash
set -e

echo "Bootstrapping environment..."
composer install

echo "Setting up env vars..."
cp user/config-sample.php user/config.php

echo "Starting dev server..."
php -S localhost:8000 &
PID=$!

sleep 2

echo "Running health check..."
curl -s http://localhost:8000/admin/install.php > /dev/null
if [ $? -eq 0 ]; then
  echo "Health check passed. Server running on PID $PID."
else
  echo "Health check failed."
  kill $PID
fi
