<?php
$host    = 'localhost';
$db      = 'smoke';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Set up the Data Source Name
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Set parameters for safe execution and error handling

try {
     // Create a new database connection object
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Terminate script execution and display the error message
     die("Connection failed: " . $e->getMessage());
}

?>