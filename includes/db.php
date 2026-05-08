<?php

function getDB(): PDO {
    static $pdo = null;

    if ($pdo) return $pdo;

    $url = getenv('DATABASE_URL');

    if ($url) {
        $parsed = parse_url($url);

        if (!$parsed || !isset($parsed['host'])) {
            die(json_encode(['error' => 'Invalid DATABASE_URL']));
        }

        $host = $parsed['host'];
        $port = $parsed['port'] ?? 5432;
        $db   = ltrim($parsed['path'], '/');
        $user = $parsed['user'] ?? '';
        $pass = $parsed['pass'] ?? '';

    } else {
        $host = getenv('PGHOST') ?: 'localhost';
        $port = getenv('PGPORT') ?: '5432';
        $db   = getenv('PGDATABASE') ?: 'postgres';
        $user = getenv('PGUSER') ?: 'postgres';
        $pass = getenv('PGPASSWORD') ?: '';
    }

    $dsn = "pgsql:host=$host;port=$port;dbname=$db";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return $pdo;

    } catch (PDOException $e) {
        die(json_encode(['error' => $e->getMessage()]));
    }
}
