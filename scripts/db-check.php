<?php
// A small DB connection test used by start.sh for debugging
$databaseUrl = getenv('DATABASE_URL') ?: null;
if ($databaseUrl) {
    $parts = parse_url($databaseUrl);
    $host = $parts['host'] ?? getenv('DB_HOST');
    $port = $parts['port'] ?? getenv('DB_PORT') ?: 3306;
    $user = $parts['user'] ?? getenv('DB_USERNAME');
    $pass = $parts['pass'] ?? getenv('DB_PASSWORD');
    $db = ltrim($parts['path'] ?? getenv('DB_DATABASE'), '/');
    $dsn = "mysql:host={$host};port={$port};dbname={$db}";
} else {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: 3306;
    $user = getenv('DB_USERNAME') ?: 'root';
    $pass = getenv('DB_PASSWORD') ?: '';
    $db = getenv('DB_DATABASE') ?: 'laravel';
    $dsn = "mysql:host={$host};port={$port};dbname={$db}";
}

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
    echo "DB test OK: Connected to {$dsn} as {$user}\n";
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_schema = DATABASE();");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Tables: " . ($count['cnt'] ?? '0') . "\n";
    exit(0);
} catch (Exception $e) {
    echo "DB test FAILED: " . $e->getMessage() . "\n";
    exit(2);
}
