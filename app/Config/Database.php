<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    public static function connect(): PDO
    {
        try {
            // Monta o DSN incluindo charset, caso ainda não esteja presente
            $dsn = $_ENV['DB_DSN'];

            if (strpos($dsn, 'charset=') === false) {
                $dsn .= (str_contains($dsn, '?') ? '&' : ';') . 'charset=utf8mb4';
            }

            $pdo = new PDO(
                $dsn,
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );

            return $pdo;

        } catch (PDOException $e) {
            die("Erro conexão DB: " . $e->getMessage());
        }
    }
}
