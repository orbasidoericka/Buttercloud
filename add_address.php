<?php
$pdo = new PDO(
    'mysql:host=shortline.proxy.rlwy.net;port=11132;dbname=railway',
    'root',
    'QolzgXlEugLziRcbjmtnpQVwYIqfGfzy'
);

echo "Adding 'address' column to orders table...\n";
$pdo->exec("ALTER TABLE orders ADD COLUMN address TEXT DEFAULT NULL AFTER contact_number");
echo "✓ Done! Address column added.\n";
