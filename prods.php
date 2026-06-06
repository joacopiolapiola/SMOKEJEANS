<?php
// 1. Establish database connection using PDO
$host = 'localhost';
$db   = 'smoke';
$user = 'root';
$pass = '';
function subirfoto() :  {
    
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// 2. Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];

    // 3. Define allowed properties for validation
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    $maxFileSize = 2 * 1024 * 1024; // 2 Megabytes
    $targetDir = "uploads/";

    // 4. Run basic security validations
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Upload failed with error code: " . $file['error']);
    }

    if (!in_array($file['type'], $allowedTypes)) {
        die("Invalid file type. Only JPG, PNG, and WEBP are allowed.");
    }

    if ($file['size'] > $maxFileSize) {
        die("File is too large. Maximum limit is 2MB.");
    }

    // 5. Generate a completely unique name to prevent accidental overwriting
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueFileName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
    $relativePath = $targetDir . $uniqueFileName;

    // Create the directory if it does not exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // 6. Move the file out of temporary storage to the target folder
    if (move_uploaded_file($file['tmp_name'], $relativePath)) {
        
        // 7. Insert the relative path reference into the database using prepared statements
        $stmt = $pdo->prepare("INSERT INTO product_images (file_path) VALUES (:file_path)");
        $stmt->execute([
            ':file_path' => $relativePath,
        ]);

        echo "The image reference has been successfully saved to the database.";
    } else {
        echo "Error moving uploaded file.";
    }
}



// Query the image path
$stmt = $pdo->query("SELECT file_path FROM product_images WHERE file_path=$relativePath");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Render the images on your page
foreach ($images as $img) {
    echo '<img src="' . htmlspecialchars($img['file_path']) . '" alt="Product Image" style="max-width:300px;"><br>';
}



?>
