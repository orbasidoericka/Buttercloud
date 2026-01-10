<?php
/**
 * Fix Railway MySQL database - Add missing primary keys, indexes, and seed data
 * Run: php fix_railway_db.php
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
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "✓ Connected successfully!\n\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// Drop and recreate tables with proper structure
$statements = [
    // Drop existing tables (in correct order due to foreign keys)
    "DROP TABLE IF EXISTS `order_items`",
    "DROP TABLE IF EXISTS `orders`",
    "DROP TABLE IF EXISTS `products`",
    "DROP TABLE IF EXISTS `users`",
    "DROP TABLE IF EXISTS `password_reset_tokens`",
    "DROP TABLE IF EXISTS `migrations`",
    "DROP TABLE IF EXISTS `sessions`",
    "DROP TABLE IF EXISTS `cache`",
    "DROP TABLE IF EXISTS `cache_locks`",
    
    // Create migrations table
    "CREATE TABLE `migrations` (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `migration` varchar(255) NOT NULL,
        `batch` int(11) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create users table
    "CREATE TABLE `users` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `password` varchar(255) NOT NULL,
        `remember_token` varchar(100) DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `users_email_unique` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create products table
    "CREATE TABLE `products` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `description` text NOT NULL,
        `price` decimal(10,2) NOT NULL,
        `image` varchar(255) DEFAULT NULL,
        `stock` int(11) NOT NULL DEFAULT 0,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create orders table
    "CREATE TABLE `orders` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `order_number` varchar(255) NOT NULL,
        `user_id` bigint(20) UNSIGNED NOT NULL,
        `customer_name` varchar(255) NOT NULL,
        `contact_number` varchar(255) NOT NULL,
        `total_amount` decimal(10,2) NOT NULL,
        `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
        `notes` text DEFAULT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `orders_user_id_foreign` (`user_id`),
        CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create order_items table
    "CREATE TABLE `order_items` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `order_id` bigint(20) UNSIGNED NOT NULL,
        `product_id` bigint(20) UNSIGNED NOT NULL,
        `product_name` varchar(255) NOT NULL,
        `price` decimal(10,2) NOT NULL,
        `quantity` int(11) NOT NULL,
        `subtotal` decimal(10,2) NOT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `order_items_order_id_foreign` (`order_id`),
        KEY `order_items_product_id_foreign` (`product_id`),
        CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
        CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create sessions table
    "CREATE TABLE `sessions` (
        `id` varchar(255) NOT NULL,
        `user_id` bigint(20) UNSIGNED DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `user_agent` text DEFAULT NULL,
        `payload` longtext NOT NULL,
        `last_activity` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `sessions_user_id_index` (`user_id`),
        KEY `sessions_last_activity_index` (`last_activity`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create cache table
    "CREATE TABLE `cache` (
        `key` varchar(255) NOT NULL,
        `value` mediumtext NOT NULL,
        `expiration` int(11) NOT NULL,
        PRIMARY KEY (`key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Create cache_locks table
    "CREATE TABLE `cache_locks` (
        `key` varchar(255) NOT NULL,
        `owner` varchar(255) NOT NULL,
        `expiration` int(11) NOT NULL,
        PRIMARY KEY (`key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Insert migration records
    "INSERT INTO `migrations` (`migration`, `batch`) VALUES 
        ('0001_01_01_000000_create_users_table', 1),
        ('0001_01_01_000001_create_cache_table', 1),
        ('2025_12_10_122903_create_products_table', 1),
        ('2025_12_10_124707_create_orders_table', 1),
        ('2025_12_10_124800_create_order_items_table', 1)",
];

echo "Recreating database schema with proper structure...\n\n";

foreach ($statements as $sql) {
    try {
        $pdo->exec($sql);
        if (preg_match('/^(DROP|CREATE|INSERT)/i', $sql, $matches)) {
            $action = strtoupper($matches[1]);
            if (preg_match('/TABLE.*`(\w+)`/i', $sql, $tableMatch)) {
                echo "✓ $action table: {$tableMatch[1]}\n";
            } elseif ($action === 'INSERT') {
                echo "✓ Inserted migration records\n";
            }
        }
    } catch (PDOException $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

// Seed products
echo "\nSeeding products...\n";

$products = [
    ['name' => 'Classic Butter Croissant', 'description' => 'Flaky, buttery croissant made with premium French butter. Golden, crispy layers with a soft, airy interior.', 'price' => 85.00, 'image' => 'images/croissant.jpg', 'stock' => 50],
    ['name' => 'Chocolate Chip Cookie', 'description' => 'Soft and chewy cookie loaded with premium chocolate chips. Baked fresh daily.', 'price' => 45.00, 'image' => 'images/cookie.jpg', 'stock' => 100],
    ['name' => 'Blueberry Muffin', 'description' => 'Moist muffin bursting with fresh blueberries and topped with a crunchy streusel.', 'price' => 65.00, 'image' => 'images/muffin.jpg', 'stock' => 40],
    ['name' => 'Cinnamon Roll', 'description' => 'Soft, gooey cinnamon roll swirled with cinnamon sugar and topped with cream cheese frosting.', 'price' => 95.00, 'image' => 'images/cinnamon-roll.jpg', 'stock' => 30],
    ['name' => 'Sourdough Bread', 'description' => 'Artisan sourdough bread with a crispy crust and tangy, chewy interior. Perfect for sandwiches or toast.', 'price' => 150.00, 'image' => 'images/sourdough.jpg', 'stock' => 20],
    ['name' => 'Red Velvet Cupcake', 'description' => 'Classic red velvet cupcake topped with smooth cream cheese frosting.', 'price' => 75.00, 'image' => 'images/cupcake.jpg', 'stock' => 60],
    ['name' => 'Almond Danish', 'description' => 'Flaky pastry filled with almond cream and topped with sliced almonds and powdered sugar.', 'price' => 90.00, 'image' => 'images/danish.jpg', 'stock' => 35],
    ['name' => 'Banana Bread', 'description' => 'Moist and flavorful banana bread made with ripe bananas and a hint of cinnamon.', 'price' => 120.00, 'image' => 'images/banana-bread.jpg', 'stock' => 25],
];

$stmt = $pdo->prepare("INSERT INTO `products` (`name`, `description`, `price`, `image`, `stock`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");

foreach ($products as $product) {
    try {
        $stmt->execute([$product['name'], $product['description'], $product['price'], $product['image'], $product['stock']]);
        echo "✓ Added product: {$product['name']}\n";
    } catch (PDOException $e) {
        echo "✗ Error adding {$product['name']}: " . $e->getMessage() . "\n";
    }
}

// Create a test user
echo "\nCreating test user...\n";
$testPassword = password_hash('password123', PASSWORD_BCRYPT);
try {
    $pdo->exec("INSERT INTO `users` (`name`, `email`, `password`, `created_at`, `updated_at`) VALUES ('Test User', 'test@buttercloud.com', '$testPassword', NOW(), NOW())");
    echo "✓ Created test user: test@buttercloud.com / password123\n";
} catch (PDOException $e) {
    echo "✗ Error creating user: " . $e->getMessage() . "\n";
}

echo "\n========================================\n";
echo "Database setup complete!\n";
echo "========================================\n\n";

// Show final state
echo "Tables in database:\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    echo "  - $table ($count rows)\n";
}

echo "\n✓ Railway database is ready!\n";
echo "\nTest login: test@buttercloud.com / password123\n";
