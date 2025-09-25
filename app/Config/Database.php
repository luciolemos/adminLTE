<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    public static function connect(): PDO
    {
        try {
            return new PDO(
                $_ENV['DB_DSN'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Erro conexão DB: " . $e->getMessage());
        }
    }
}
