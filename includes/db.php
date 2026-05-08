<?php
// ==========================================
// ربط قاعدة البيانات PostgreSQL باستخدام PDO
// ==========================================

function getDB(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = getenv('DATABASE_URL');
    if (!$dsn) {
        // بناء عنوان الاتصال من المتغيرات الفردية
        $host     = getenv('PGHOST')     ?: 'localhost';
        $port     = getenv('PGPORT')     ?: '5432';
        $dbname   = getenv('PGDATABASE') ?: 'postgres';
        $user     = getenv('PGUSER')     ?: 'postgres';
        $password = getenv('PGPASSWORD') ?: '';
        $dsn      = "pgsql:host=$host;port=$port;dbname=$dbname";
    } else {
        // تحويل postgresql:// إلى DSN صالح لـ PDO
        $parsed   = parse_url($dsn);
        $host     = $parsed['host'];
        $port     = $parsed['port'] ?? 5432;
        $dbname   = ltrim($parsed['path'], '/');
        $user     = $parsed['user'];
        $password = $parsed['pass'];
        $dsn      = "pgsql:host=$host;port=$port;dbname=$dbname";
    }

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
    }

    return $pdo;
}
