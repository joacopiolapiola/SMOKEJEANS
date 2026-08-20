<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
    require_once "config.php";
    
    /*
    ESTA ES LA SUBIDA DE CATEGORIAS 
    */
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

/*
    ESTA ES LA SUBIDA DE PRODUCTOS, CON MULTIPLES IMAGENES Y CATEGORIAS
    */

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_prod'])) {

        if (!isset($_FILES['imagenes']) || !is_array($_FILES['imagenes']['name'])) {
            die("No se recibió ninguna imagen.");
        }
        
        $cats = isset($_POST['categorias']) && is_array($_POST['categorias'])
            ? $_POST['categorias']
            : [];
        $nombreprod = trim($_POST['nombre'] ?? '');
        $descripcionprod = trim($_POST['descripcion'] ?? '');
        $precioprod = $_POST['precio'] ?? '';

        if ($nombreprod === '' || !is_numeric($precioprod) || $precioprod < 0) {
            die("Los datos del precio del producto no son válidos.");
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $maxFileSize = 2 * 1024 * 1024; // 2 Megabytes
        $targetDir = "uploads/";
        $fileInfo = new finfo(FILEINFO_MIME_TYPE);

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                die("No se pudo crear la carpeta de imágenes.");
            }
        }

        $pdo->beginTransaction();
        $savedFiles = [];

        try {
            $productStmt = $pdo->prepare(
                "INSERT INTO productos (nombre, descripcion, precio)
                 VALUES (:nombre, :descripcion, :precio)"
            );
            $productStmt->execute([
                ':nombre' => $nombreprod,
                ':descripcion' => $descripcionprod,
                ':precio' => $precioprod,
            ]);
            $productId = (int) $pdo->lastInsertId();

            $imageStmt = $pdo->prepare(
                "INSERT INTO product_images (producto_id, file_path)
                 VALUES (:producto_id, :file_path)"
            );

            foreach ($_FILES['imagenes']['name'] as $index => $originalName) {
                $file = [
                    'tmp_name' => $_FILES['imagenes']['tmp_name'][$index],
                    'error' => $_FILES['imagenes']['error'][$index],
                    'size' => $_FILES['imagenes']['size'][$index],
                ];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new RuntimeException("Error al subir la imagen: " . $file['error']);
                }

                $mimeType = $fileInfo->file($file['tmp_name']);
                if (!in_array($mimeType, $allowedTypes, true)) {
                    throw new RuntimeException("Tipo de imagen no válido. Solo se permiten JPG, PNG y WEBP.");
                }

                if ($file['size'] > $maxFileSize) {
                    throw new RuntimeException("Una imagen supera el límite máximo de 2 MB.");
                }

                $extensions = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                ];
                $uniqueFileName = bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
                $relativePath = $targetDir . $uniqueFileName;

                if (!move_uploaded_file($file['tmp_name'], $relativePath)) {
                    throw new RuntimeException("No se pudo guardar una de las imágenes.");
                }

                $imageStmt->execute([
                    ':producto_id' => $productId,
                    ':file_path' => $relativePath,
                ]);
                $savedFiles[] = $relativePath;
            }
 
            //pivot con categorias

            $categoryStmt = $pdo->prepare(
                "INSERT INTO prod_categoria (prod_id, categoria_id)
                 VALUES (:prod_id, :categoria_id)"
            );
            foreach ($cats as $categoryId) {
                $categoryStmt->execute([
                    ':prod_id' => $productId,
                    ':categoria_id' => (int) $categoryId,
                ]);
            }

            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            foreach ($savedFiles as $savedFile) {
                if (is_file($savedFile)) {
                    unlink($savedFile);
                }
            }
            die($exception->getMessage());
        }

        header("Location: /SMOKEJEANS/index.php?cat=all&buscar=");
        exit;
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
                        <a href="index.php">
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
            $stmt = $pdo->query("SELECT producto_id, file_path FROM product_images");
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        ?>
    </section>
    <!-- SUBIR PRODUCTO formulario -->
    <section class="productos">

        <h3>Subir producto</h3>

        <form action="" method="POST" enctype="multipart/form-data">
            <div id="listadeinputsimagen">
                <div class="imagen-row">
                    <input type="file" name="imagenes[]" accept="image/*" required>
                    <button type="button" class="agregar-imagen" hidden>Agregar otra imagen</button>
                </div>
            </div>

            <script>
            const imageContainer = document.getElementById('listadeinputsimagen');

            imageContainer.addEventListener('change', (event) => {
                if (event.target.matches('input[type="file"]')) {
                    event.target.parentElement.querySelector('.agregar-imagen').hidden = false;
                }
            });

            imageContainer.addEventListener('click', (event) => {
                if (!event.target.matches('.agregar-imagen')) {
                    return;
                }

                const row = document.createElement('div');
                row.className = 'imagen-row';
                row.innerHTML = `
                    <input type="file" name="imagenes[]" accept="image/*" required>
                    <button type="button" class="agregar-imagen" hidden>Agregar otra imagen</button> 
                `;
                imageContainer.appendChild(row);
            });
            </script>

        <?php foreach ($cat as $row): ?>
        <label>
        <input type="checkbox" name="categorias[]" value="<?= htmlspecialchars($row['id']) ?>">
        <?= htmlspecialchars($row['nombre']) ?>
        </label><br>
        <?php endforeach; ?>
            
            <input 
            type="text" 
            id="nombre"
            name ="nombre"
            > <br>
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


    

    <!-- SUBIR CATEGORÍA FORMUARIO-->
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
