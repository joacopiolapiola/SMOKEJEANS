<!DOCTYPE html>
<html lang="es">
<head>
    <?php 
            //rescatamos todas las categorias existentes
        require_once 'config.php';
        $categorias = $pdo->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>busqueda</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar"> 
    <form action="" method="get">
        <select name="cat" id="">
            <option value="all">Todas las categorías</option>
            <?php  foreach($categorias as $c){
                echo "<option value=\"" . htmlspecialchars($c['id'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($c['nombre'], ENT_QUOTES, 'UTF-8') . "</option>";
            } ?>
        </select>
        <button type="submit" name="buscar">Buscar</button>
    </form>
</nav>
    <?php 
    //Buscamos todos los productos de la categoria elegida.
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['buscar'])) {
        $categoriaSeleccionada = $_GET['cat'] ?? '';
        $productos = [];

        if ($categoriaSeleccionada === 'all') {
            $stmt = $pdo->query(
                'SELECT p.*, pi.file_path AS imagen
                 FROM productos AS p
                 LEFT JOIN product_images AS pi ON pi.producto_id = p.id
                 ORDER BY p.id, pi.id'
            );
        } else {
            $catsbuscar = filter_var($categoriaSeleccionada, FILTER_VALIDATE_INT);

            if ($catsbuscar === false || $catsbuscar === null) {
                $stmt = null;
            } else {
                $stmt = $pdo->prepare(
                    'SELECT p.*, pi.file_path AS imagen
                     FROM productos AS p
                     INNER JOIN prod_categoria AS pc ON pc.prod_id = p.id
                     LEFT JOIN product_images AS pi ON pi.producto_id = p.id
                     WHERE pc.categoria_id = :categoria_id
                     ORDER BY p.id, pi.id'
                );
                $stmt->execute(['categoria_id' => $catsbuscar]);
            }
        }

        if ($stmt !== null) {
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($filas as $fila) {
                $productoId = $fila['id'];

                if (!isset($productos[$productoId])) {
                    $productos[$productoId] = [
                        'id' => $fila['id'],
                        'nombre' => $fila['nombre'],
                        'descripcion' => $fila['descripcion'],
                        'precio' => $fila['precio'],
                        'imagenes' => [],
                    ];
                }

                if ($fila['imagen'] !== null) {
                    $productos[$productoId]['imagenes'][] = $fila['imagen'];
                }
            }
        }
    }
    ?>

    <?php if (isset($productos)): ?>
        <main class="resultados">
            <?php if (empty($productos)): ?>
                <p>No hay productos en esta categoría.</p>
            <?php else: ?>
                <?php foreach ($productos as $producto): ?>
                    <article class="producto">
                        <h2><?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><?= htmlspecialchars($producto['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="precio">
                            <?= htmlspecialchars(number_format((float) $producto['precio'], 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?> €
                        </p>

                        <div class="imagenes-producto">
                            <?php if (empty($producto['imagenes'])): ?>
                                <p>Este producto no tiene imágenes.</p>
                            <?php else: ?>
                                <?php foreach ($producto['imagenes'] as $imagen): ?>
                                    <img src="<?= htmlspecialchars($imagen, ENT_QUOTES, 'UTF-8') ?>"
                                         alt="<?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    <?php endif; ?>
 

</body>
</html>