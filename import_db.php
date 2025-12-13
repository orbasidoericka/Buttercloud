<?php

$host = 'shortline.proxy.rlwy.net';
$port = 56871;
$database = 'railway';
$username = 'root';
$password = 'SUTzMXEzjpZDJzCrNGOUqfXCQPMLffwn';

$sqlFile = __DIR__ . '/database/legends.sql';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "Connected to Railway MySQL successfully!\n";
    
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        die("Error reading SQL file.\n");
    }
    
    echo "Importing SQL file...\n";
    
    // Remove comments and split properly
    $lines = explode("\n", $sql);
    $tempLine = '';
    $statements = [];
    
    foreach ($lines as $line) {
        // Skip comments and empty lines
        if (substr(trim($line), 0, 2) == '--' || trim($line) == '' || substr(trim($line), 0, 2) == '/*') {
            continue;
        }
        
        $tempLine .= $line;
        
        if (substr(trim($line), -1, 1) == ';') {
            $statements[] = $tempLine;
            $tempLine = '';
        }
    }
    
    $count = 0;
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $count++;
                echo ".";
            } catch (PDOException $e) {
                echo "\nError on statement: " . substr($statement, 0, 100) . "...\n";
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\nSuccessfully imported $count SQL statements!\n";
    echo "Database import complete.\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
