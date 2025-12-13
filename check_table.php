<?php

$host = 'shortline.proxy.rlwy.net';
$port = 56871;
$database = 'railway';
$username = 'root';
$password = 'SUTzMXEzjpZDJzCrNGOUqfXCQPMLffwn';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->query("SHOW CREATE TABLE products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo $result['Create Table'];
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
