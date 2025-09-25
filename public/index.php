<?php

// public/index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
require_once dirname(__DIR__) . '/app/Helpers/flash.php';


// ⚠️ Exibe erros apenas em ambiente de desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Core\App;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

$app = new App();
