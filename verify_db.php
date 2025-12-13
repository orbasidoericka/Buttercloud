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
    
    echo "Tables in Railway MySQL database:\n";
    echo "==================================\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    
    echo "\nProducts:\n";
    echo "=========\n";
    $stmt = $pdo->query("SELECT id, name, price, stock FROM products LIMIT 10");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        echo sprintf("ID: %d | %s | ₱%d | Stock: %d\n", 
            $product['id'], 
            $product['name'], 
            $product['price'], 
            $product['stock']
        );
    }
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
