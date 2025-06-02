<?php

use Symfony\Component\HttpFoundation\Request;
use App\Kernel;

// Vercel deployment için gerekli environment variables'ları ayarla
if (!isset($_ENV['APP_ENV'])) {
    $_ENV['APP_ENV'] = 'prod';
    $_SERVER['APP_ENV'] = 'prod';
}

if (!isset($_ENV['APP_DEBUG'])) {
    $_ENV['APP_DEBUG'] = '0';
    $_SERVER['APP_DEBUG'] = '0';
}

if (!isset($_ENV['APP_SECRET'])) {
    $_ENV['APP_SECRET'] = 'vercel-prod-secret-' . hash('sha256', __DIR__ . 'symfony-vercel');
    $_SERVER['APP_SECRET'] = $_ENV['APP_SECRET'];
}

// .env dosyasını skip et
if (!file_exists(dirname(__DIR__) . '/.env')) {
    $_ENV['SYMFONY_DOTENV_VARS'] = '';
    $_SERVER['SYMFONY_DOTENV_VARS'] = '';
}

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'] ?? $_ENV['APP_ENV'], (bool) ($context['APP_DEBUG'] ?? $_ENV['APP_DEBUG']));

    return $kernel;
};
