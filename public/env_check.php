<?php
header('Content-Type: text/plain');
echo "Checking Environment Variables:\n\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NOT SET') . "\n";
echo "DB_DATABASE: " . (getenv('DB_DATABASE') ?: 'NOT SET') . "\n";
echo "APP_KEY: " . (getenv('APP_KEY') ? 'SET (hidden for security)' : 'NOT SET') . "\n";
echo "APP_ENV: " . (getenv('APP_ENV') ?: 'NOT SET') . "\n";
echo "\nServer Data:\n";
print_r($_SERVER);
