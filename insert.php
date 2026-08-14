<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear admins</title>
</head>
<body>

<form action="" method="POST">

    <input
        type="text"
        name="nombre"
        placeholder="Nombre del admin"
            >
    <input
        type="text"
        name="contra"
        placeholder="Contraseña"
            >
            <input
            type="submit"
            value="Mandale"
            name="submit_admin"
            >
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_admin'])) {
    require_once "config.php";
    $nombre1=$_REQUEST['nombre'];
    $contra1=$_REQUEST['contra'];

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
    ?><p>
    <?php echo "ultima  ID: <br>" . $pdo->lastInsertId(); ?>
      </p>
<?php
    $results = $pdo->query($sql2)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        echo "NOMBRE:   ",$row['nombre']," ID:      ", $row['id'],"PASSWORD:        ", $row['password'] . "<br>";
    }
}
    ?>

</body>
</html>