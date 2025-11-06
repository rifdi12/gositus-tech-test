<?php

// Simple test script for database connection
require_once 'vendor/autoload.php';

// Database configuration
$config = [
    'DSN'      => '',
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'elibrary_db',
    'DBDriver' => 'MySQLi',
    'DBPrefix' => '',
    'pConnect' => false,
    'DBDebug'  => true,
    'charset'  => 'utf8mb4',
    'DBCollat' => 'utf8mb4_general_ci',
    'swapPre'  => '',
    'encrypt'  => false,
    'compress' => false,
    'strictOn' => false,
    'failover' => [],
    'port'     => 3306,
];

try {
    // Create database connection
    $mysqli = new mysqli($config['hostname'], $config['username'], $config['password']);
    
    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "✓ MySQL connection successful\n";
    
    // Check if database exists
    $result = $mysqli->query("SHOW DATABASES LIKE 'elibrary_db'");
    if ($result->num_rows > 0) {
        echo "✓ Database 'elibrary_db' exists\n";
    } else {
        echo "✗ Database 'elibrary_db' not found\n";
        echo "Creating database...\n";
        if ($mysqli->query("CREATE DATABASE elibrary_db")) {
            echo "✓ Database 'elibrary_db' created successfully\n";
        } else {
            echo "✗ Error creating database: " . $mysqli->error . "\n";
        }
    }
    
    // Select database
    $mysqli->select_db('elibrary_db');
    
    // Check if tables exist
    $tables = ['users', 'books', 'favorites'];
    foreach ($tables as $table) {
        $result = $mysqli->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "✓ Table '$table' exists\n";
        } else {
            echo "✗ Table '$table' not found - run migrations\n";
        }
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n--- E-Library Setup Instructions ---\n";
echo "1. Make sure MySQL is running\n";
echo "2. Run: php spark migrate\n";
echo "3. Run: php spark db:seed UserSeeder\n";
echo "4. Start server: php spark serve\n";
echo "\nDemo accounts:\n";
echo "Admin: admin@elibrary.com / Admin123\n";
echo "User:  user@elibrary.com / User123\n";