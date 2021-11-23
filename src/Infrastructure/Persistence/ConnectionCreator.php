<?php

namespace Alura\Pdo\Infrastructure\Persistence;

use PDO;

class ConnectionCreator
{
    public static function createConnection() : PDO
    {
        $db_path = __DIR__ . '/../../../banco.sqlite';

        return new PDO('sqlite:' . $db_path);
    }
}