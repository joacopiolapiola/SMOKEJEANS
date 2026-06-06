<?php
$host    = 'localhost';
$db      = 'smoke';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';
$nombre1=$_REQUEST['nombre'];
$contra1=$_REQUEST['contra'];

// Set up the Data Source Name
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Set parameters for safe execution and error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     // Create a new database connection object
     $pdo = new PDO($dsn, $user, $pass, $options);
     echo "Connected to the database successfully!";
} catch (\PDOException $e) {
     // Terminate script execution and display the error message
     die("Connection failed: " . $e->getMessage());
}

$sql = "INSERT INTO admins (nombre, password) VALUES (:nombre, :password)";
$sql2= "SELECT * FROM admins";

// 2. Prepare the statement
$stmt = $pdo->prepare($sql);

// 3. Define the data you want to insert
$data = [
    'nombre'   => $nombre1,
    'password' => $contra1,
];

// 4. Execute the statement by passing the data array
$stmt->execute($data);

echo "Data written successfully! Last inserted ID: <br>" . $pdo->lastInsertId();

$results = $pdo->query($sql2)->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $row) {
    echo "NOMBRE:   ",$row['nombre']," ID:      ", $row['id'],"PASSWORD:        ", $row['password'] . "<br>";
}


?>
