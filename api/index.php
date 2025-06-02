<?php

// Vercel deployment için environment setup - çok erken yapılmalı
$_ENV['APP_ENV'] = 'prod';
$_SERVER['APP_ENV'] = 'prod';
$_ENV['APP_DEBUG'] = '0';
$_SERVER['APP_DEBUG'] = '0';
$_ENV['APP_SECRET'] = 'vercel-prod-secret-' . hash('sha256', __DIR__ . 'symfony-vercel');
$_SERVER['APP_SECRET'] = $_ENV['APP_SECRET'];

// .env dosyası yoksa production env dosyasını kopyala
$envPath = dirname(__DIR__) . '/.env';
$prodEnvPath = dirname(__DIR__) . '/env.production';

if (!file_exists($envPath) && file_exists($prodEnvPath)) {
    copy($prodEnvPath, $envPath);
}

// Symfony'nin .env dosyası yüklememesi için
$_ENV['SYMFONY_SKIP_DOTENV'] = '1';
$_SERVER['SYMFONY_SKIP_DOTENV'] = '1';
$_ENV['SYMFONY_DOTENV_VARS'] = '';
$_SERVER['SYMFONY_DOTENV_VARS'] = '';

use Symfony\Component\HttpFoundation\Request;
use App\Kernel;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($_ENV['APP_ENV'], (bool) $_ENV['APP_DEBUG']);

    return $kernel;
};
