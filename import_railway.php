<?php
/**
 * Import legends.sql to Railway MySQL
 * Run: php import_railway.php
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

// Read the SQL file
$sqlFile = __DIR__ . '/database/legends.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);
echo "✓ Read SQL file (" . strlen($sql) . " bytes)\n\n";

// Remove comments and clean up SQL
$sql = preg_replace('/--.*$/m', '', $sql);  // Remove single-line comments
$sql = preg_replace('/\/\*.*?\*\//s', '', $sql);  // Remove multi-line comments
$sql = preg_replace('/\/\*!.*?\*\//s', '', $sql);  // Remove MySQL conditional comments

// Split into individual statements
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($s) => !empty($s) && strlen(trim($s)) > 5
);

echo "Executing " . count($statements) . " statements...\n\n";

$success = 0;
$failed = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement) || strlen($statement) < 5) continue;
    
    // Skip transaction commands
    if (preg_match('/^(SET |START |COMMIT)/i', $statement)) {
        continue;
    }
    
    try {
        $pdo->exec($statement);
        
        // Show what was executed
        if (preg_match('/CREATE TABLE `?(\w+)`?/i', $statement, $matches)) {
            echo "✓ Created table: {$matches[1]}\n";
        } elseif (preg_match('/ALTER TABLE `?(\w+)`?/i', $statement, $matches)) {
            echo "✓ Altered table: {$matches[1]}\n";
        } elseif (preg_match('/INSERT INTO `?(\w+)`?/i', $statement, $matches)) {
            echo "✓ Inserted into: {$matches[1]}\n";
        }
        $success++;
    } catch (PDOException $e) {
        // Ignore "table already exists" errors
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "⚠ Table already exists, skipping...\n";
        } else {
            echo "✗ Error: " . $e->getMessage() . "\n";
            echo "  Statement: " . substr($statement, 0, 80) . "...\n";
            $failed++;
        }
    }
}

echo "\n========================================\n";
echo "Import complete!\n";
echo "✓ Successful: $success\n";
echo "✗ Failed: $failed\n";
echo "========================================\n\n";

// Show tables
echo "Tables in database:\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    echo "  - $table ($count rows)\n";
}

echo "\n✓ Database migration complete!\n";
