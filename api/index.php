<?php

// Vercel için çok erken environment setup - Symfony yüklenmeden önce
putenv('APP_ENV=prod');
putenv('APP_DEBUG=1'); // Debug modunu geçici olarak aç
putenv('APP_SECRET=vercel-prod-secret-' . hash('sha256', __DIR__ . 'symfony-vercel'));
putenv('DATABASE_URL=sqlite:///:memory:');

$_ENV['APP_ENV'] = 'prod';
$_ENV['APP_DEBUG'] = '1'; // Debug modunu geçici olarak aç
$_ENV['APP_SECRET'] = 'vercel-prod-secret-' . hash('sha256', __DIR__ . 'symfony-vercel');
$_ENV['DATABASE_URL'] = 'sqlite:///:memory:';

$_SERVER['APP_ENV'] = 'prod';
$_SERVER['APP_DEBUG'] = '1'; // Debug modunu geçici olarak aç
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
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;

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
$kernel = new VercelKernel('prod', true); // Debug modunu aç

// Vercel'de database'i otomatik kur - in-memory SQLite
try {
    $kernel->boot();
    $container = $kernel->getContainer();

    // Entity Manager'ı al
    if ($container->has('doctrine.orm.entity_manager')) {
        $entityManager = $container->get('doctrine.orm.entity_manager');

        // Database schema'yı oluştur - in-memory database her zaman boş başlar
        $connection = $entityManager->getConnection();

        // Her request'te tabloları oluştur (in-memory database)
        $sql = "
            CREATE TABLE IF NOT EXISTS user (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email VARCHAR(180) NOT NULL UNIQUE,
                roles TEXT NOT NULL,
                password VARCHAR(255) NOT NULL,
                is_verified BOOLEAN NOT NULL DEFAULT 0,
                name VARCHAR(255) DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS messenger_messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                body TEXT NOT NULL,
                headers TEXT NOT NULL,
                queue_name VARCHAR(190) NOT NULL,
                created_at DATETIME NOT NULL,
                available_at DATETIME NOT NULL,
                delivered_at DATETIME DEFAULT NULL
            );
            
            CREATE TABLE IF NOT EXISTS doctrine_migration_versions (
                version VARCHAR(191) NOT NULL PRIMARY KEY,
                executed_at DATETIME DEFAULT NULL,
                execution_time INTEGER DEFAULT NULL
            );
        ";

        $connection->executeStatement($sql);
    }
} catch (Exception $e) {
    // Migration hatası varsa devam et
    error_log("Database setup error: " . $e->getMessage());
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
