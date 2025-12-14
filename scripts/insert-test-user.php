<?php
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST');
$port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306;
$db = getenv('DB_DATABASE') ?: getenv('MYSQLDATABASE');
$user = getenv('DB_USERNAME') ?: getenv('MYSQLUSER');
$pass = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD');
$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    fwrite(STDERR, "Connection error: " . $e->getMessage() . "\n");
    exit(1);
}
$name = 'Test User';
$email = 'test+' . time() . '@example.com';
$password = password_hash('password123', PASSWORD_BCRYPT);
$now = date('Y-m-d H:i:s');
$stmt = $pdo->prepare('INSERT INTO users (name,email,password,created_at,updated_at) VALUES (?,?,?,?,?)');
$stmt->execute([$name, $email, $password, $now, $now]);
echo "Inserted user with id: " . $pdo->lastInsertId() . PHP_EOL;
