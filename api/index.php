<?php

// Vercel için çok erken environment setup - Symfony yüklenmeden önce
putenv('APP_ENV=prod');
putenv('APP_DEBUG=1');
putenv('APP_SECRET=vercel-prod-secret-' . hash('sha256', __DIR__ . 'symfony-vercel'));
putenv('DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db');

$_ENV['APP_ENV'] = 'prod';
$_ENV['APP_DEBUG'] = '1';
$_ENV['APP_SECRET'] = 'vercel-prod-secret-' . hash('sha256', __DIR__ . 'symfony-vercel');
$_ENV['DATABASE_URL'] = 'sqlite:///tmp/symfony-database.db';

$_SERVER['APP_ENV'] = 'prod';
$_SERVER['APP_DEBUG'] = '1';
$_SERVER['APP_SECRET'] = $_ENV['APP_SECRET'];
$_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'];

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

// Vercel için gerekli dizinleri oluştur
@mkdir('/tmp/symfony-cache', 0755, true);
@mkdir('/tmp/symfony-cache/prod', 0755, true);
@mkdir('/tmp/symfony-logs', 0755, true);

// Custom Kernel class for Vercel
class VercelKernel extends Kernel
{
    public function getCacheDir(): string
    {
        return '/tmp/symfony-cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return '/tmp/symfony-logs';
    }
}

// Kernel'i direkt oluştur ve handle et
$kernel = new VercelKernel('prod', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
