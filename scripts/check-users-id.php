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
$row = $pdo->query('SELECT COUNT(*) AS cnt, MAX(id) AS maxid FROM users')->fetch(PDO::FETCH_ASSOC);
$dups = $pdo->query('SELECT id, COUNT(*) AS c FROM users GROUP BY id HAVING c > 1')->fetchAll(PDO::FETCH_ASSOC);
print_r($row);
if (!empty($dups)) {
    echo "Duplicates found:\n"; print_r($dups);
} else {
    echo "No duplicate ids.\n";
}
$nulls = $pdo->query('SELECT COUNT(*) FROM users WHERE id IS NULL')->fetchColumn();
echo "Null id count: $nulls\n";
