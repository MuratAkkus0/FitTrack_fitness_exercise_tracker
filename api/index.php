<?php

// Vercel için çok erken environment setup - Symfony yüklenmeden önce
putenv('APP_ENV=prod');
putenv('APP_DEBUG=0');
putenv('APP_SECRET=vercel-prod-secret-' . hash('sha256', __DIR__ . 'symfony-vercel'));

$_ENV['APP_ENV'] = 'prod';
$_ENV['APP_DEBUG'] = '0';
$_ENV['APP_SECRET'] = 'vercel-prod-secret-' . hash('sha256', __DIR__ . 'symfony-vercel');

$_SERVER['APP_ENV'] = 'prod';
$_SERVER['APP_DEBUG'] = '0';
$_SERVER['APP_SECRET'] = $_ENV['APP_SECRET'];

// .env dosyası yoksa production env dosyasını kopyala
$envPath = dirname(__DIR__) . '/.env';
$prodEnvPath = dirname(__DIR__) . '/env.production';

if (!file_exists($envPath) && file_exists($prodEnvPath)) {
    copy($prodEnvPath, $envPath);
}

// Symfony Dotenv'i tamamen devre dışı bırak
$_ENV['SYMFONY_SKIP_DOTENV'] = '1';
$_SERVER['SYMFONY_SKIP_DOTENV'] = '1';

use Symfony\Component\HttpFoundation\Request;
use App\Kernel;

// Vendor autoload'u manuel yükle - runtime değil
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Kernel'i direkt oluştur ve handle et
$kernel = new Kernel('prod', false);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
