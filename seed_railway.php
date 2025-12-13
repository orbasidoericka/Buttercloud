<?php

$host = 'shortline.proxy.rlwy.net';
$port = 56871;
$database = 'railway';
$username = 'root';
$password = 'SUTzMXEzjpZDJzCrNGOUqfXCQPMLffwn';

$products = [
    ['name' => 'Butter Croissant', 'description' => 'Flaky, buttery croissant baked fresh daily', 'price' => 75, 'stock' => 50, 'image' => 'ButterCroissant.png'],
    ['name' => 'Cheese Danish', 'description' => 'Sweet danish pastry with cream cheese filling', 'price' => 85, 'stock' => 40, 'image' => 'danish.jpg'],
    ['name' => 'Blueberry Muffin', 'description' => 'Moist muffin loaded with fresh blueberries', 'price' => 60, 'stock' => 60, 'image' => 'bluebery.jpg'],
    ['name' => 'Cinnamon Roll', 'description' => 'Warm cinnamon roll with cream cheese frosting', 'price' => 95, 'stock' => 35, 'image' => 'cinnamon.jpg'],
    ['name' => 'Belgian Waffle', 'description' => 'Crispy waffle served with maple syrup', 'price' => 120, 'stock' => 25, 'image' => 'waffle.jpg'],
    ['name' => 'Apple Turnover', 'description' => 'Puff pastry filled with spiced apple filling', 'price' => 80, 'stock' => 45, 'image' => 'appleutrnover.jpg'],
    ['name' => 'Lemon Tart', 'description' => 'Tangy lemon curd in a buttery tart shell', 'price' => 100, 'stock' => 30, 'image' => 'lemon-tart.jpg'],
    ['name' => 'Chocolate Eclair', 'description' => 'Choux pastry filled with cream and chocolate glaze', 'price' => 90, 'stock' => 40, 'image' => 'choco.png'],
    ['name' => 'Malunggay Pandesal', 'description' => 'Filipino bread roll with malunggay (moringa)', 'price' => 50, 'stock' => 100, 'image' => 'malunggay.jpg'],
];

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "Connected to Railway MySQL!\n";
    
    // Clear existing products
    $pdo->exec("DELETE FROM products");
    echo "Cleared existing products.\n";
    
    // Insert products
    $stmt = $pdo->prepare(
        "INSERT INTO products (name, description, price, stock, image, created_at, updated_at) 
         VALUES (?, ?, ?, ?, ?, NOW(), NOW())"
    );
    
    $count = 0;
    foreach ($products as $product) {
        $stmt->execute([
            $product['name'],
            $product['description'],
            $product['price'],
            $product['stock'],
            $product['image']
        ]);
        $count++;
        echo "Added: {$product['name']}\n";
    }
    
    echo "\nSuccessfully seeded $count products to Railway!\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
