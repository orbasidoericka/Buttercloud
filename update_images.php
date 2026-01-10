<?php
/**
 * Update product images in Railway database
 */

$host = 'shortline.proxy.rlwy.net';
$port = 11132;
$database = 'railway';
$username = 'root';
$password = 'QolzgXlEugLziRcbjmtnpQVwYIqfGfzy';

echo "Connecting to Railway MySQL...\n";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Connected!\n\n";
} catch (PDOException $e) {
    die("Failed: " . $e->getMessage() . "\n");
}

// Update product images
$updates = [
    ['name' => 'Classic Butter Croissant', 'image' => 'ButterCroissant.png'],
    ['name' => 'Butter Croissant', 'image' => 'ButterCroissant.png'],
    ['name' => 'Chocolate Chip Cookie', 'image' => 'choco.png'],
    ['name' => 'Chocolate Danish', 'image' => 'danish.jpg'],
    ['name' => 'Blueberry Muffin', 'image' => 'bluebery.jpg'],
    ['name' => 'Cinnamon Roll', 'image' => 'cinnamon.jpg'],
    ['name' => 'Almond Croissant', 'image' => 'waffle.jpg'],
    ['name' => 'Apple Turnover', 'image' => 'appleutrnover.jpg'],
    ['name' => 'Lemon Tart', 'image' => 'lemon-tart.jpg'],
    ['name' => 'Chocolate Chip Scone', 'image' => 'choco.png'],
    ['name' => 'Malunggay Pandesal', 'image' => 'malunggay.jpg'],
    ['name' => 'Sourdough Bread', 'image' => 'ButterCroissant.png'],
    ['name' => 'Red Velvet Cupcake', 'image' => 'choco.png'],
    ['name' => 'Almond Danish', 'image' => 'danish.jpg'],
    ['name' => 'Banana Bread', 'image' => 'cinnamon.jpg'],
];

echo "Updating product images...\n";
$stmt = $pdo->prepare("UPDATE products SET image = ? WHERE name = ?");

foreach ($updates as $update) {
    try {
        $stmt->execute([$update['image'], $update['name']]);
        if ($stmt->rowCount() > 0) {
            echo "✓ Updated: {$update['name']} → {$update['image']}\n";
        }
    } catch (PDOException $e) {
        echo "✗ Error updating {$update['name']}: " . $e->getMessage() . "\n";
    }
}

// Show all products with images
echo "\n========================================\n";
echo "Current products:\n";
$products = $pdo->query("SELECT id, name, image FROM products ORDER BY id")->fetchAll();
foreach ($products as $product) {
    echo "  {$product['id']}. {$product['name']} → {$product['image']}\n";
}

echo "\n✓ Done!\n";
