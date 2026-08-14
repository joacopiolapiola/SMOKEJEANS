<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
        require_once "config.php";
        
        $cat = $pdo->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
        
        
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_cat'])) {
            
            $nombre1=$_REQUEST['nombre'];
            
            $sql = "INSERT INTO categorias (nombre) VALUES (:nombre)";
            
            // 2. Prepare the statement
            $stmt = $pdo->prepare($sql);
            
            // 3. Define the data you want to insert
            $data = [
                'nombre'   => $nombre1
                ];
                
            // 4. Execute the statement by passing the data array
            $stmt->execute($data);
        
                header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        
        } ?>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>subida de productos</title>
        <link rel="stylesheet" href="style.css">
    </head>
    
<body>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_prod'])) {

        $file = $_POST['image1'];
        echo "<p> ENTRAMOOOOOO </p>";
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
        
            echo "<p> se guardo la  imagden bienn guardado de imagen</p>";
        
        } else {
          
        echo "<p> NO se guardo la imagen. </p>";
        
        }
    }
?>

<!-- NAVBAR -->
    <nav class="navbar">
        
        <h2>Mi página</h2>
        
        <ul>
            <li>
                <a href="index.php">Inicio</a>
            </li>
            
            <li class="buscar">
                <a href="#" class="icono-buscar" title="Buscar">🔍</a>
                
                <ul class="submenu">
                    <li>
                        <a href="buscar_categoria.php">
                            Por categoría
                        </a>
                    </li>
                    
                    <li>
                        <a href="buscar_articulo.php">
                            Por artículo
                        </a>
                    </li>
                </ul>
            </li>
            
        </nav>
        
        
    <section>
        <?php       

    $stmt = $pdo->query("SELECT file_path FROM product_images");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);                            

    foreach ($images as $img) {
        echo '<img src="' . htmlspecialchars($img['file_path']) . '" alt="Product Image" style="max-width:300px;"><br>';
        }
    ?>
    </section>
    <!-- SUBIR PRODUCTO -->
    <section class="productos">

        <h3>Subir producto</h3>

        <form action="" method="POST" enctype="multipart/form-data">

            <input type="file" name="image1" accept="image/*" required><br>

            <input type="file" name="image2" accept="image/*"><br>

            <input type="file" name="image3" accept="image/*"><br>

            <input type="file" name="image4" accept="image/*"><br>



        <?php foreach ($cat as $row): ?>
        <label>
        <input type="checkbox" name="categorias[]" value="<?= htmlspecialchars($row['id']) ?>">
        <?= htmlspecialchars($row['nombre']) ?>
        </label><br>
        <?php endforeach; ?>

            <input
                type="number"
                name="precio"
                id="precio"
                placeholder="20000"
            ><br>

            <input
                type="text"
                name="descripcion"
                id="descripcion"
                placeholder="Descripción"
            ><br>


            <button type="submit" name="submit_prod">
                Subir producto
            </button>

        </form>

    </section>


    <!-- SUBIR CATEGORÍA -->
    <section class="categorias">

        <h3>Subir categoría</h3>

        <form action="" method="POST">

            <input
                type="text"
                name="nombre"
                placeholder="Nombre de la categoría"
            >

            <input
                type="submit"
                value="Mandale"
                name="submit_cat"
            >

        </form>

    </section>

</body>
</html>
