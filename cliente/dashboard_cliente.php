<?php
session_start();
include('../conexion.php');

// Verificar si el usuario está autenticado y es cliente
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'cliente') {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

//echo "Valor de username: " . $username;
// Consultar datos del cliente usando JOIN
$sql = "SELECT c.*, u.nombre_usuario 
        FROM clientes c
        INNER JOIN usuarios u ON c.nombre_usuario = u.nombre_usuario
        WHERE u.nombre_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $cliente = $result->fetch_assoc();
    $cliente_nombre = htmlspecialchars($cliente['nombre_completo']);
} else { 
    echo "No se encontraron datos del cliente.";
    exit();
}

// Mostrar la vista HTML con los datos dinámicos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Cliente</title>
    <link rel="stylesheet" href="../styles/main.css">
</head>
<body>
    <header>
        <h1>Bienvenido a tu panel de cliente</h1>
    </header>
    <nav>
        <ul>
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="pedidos.php">Pedidos</a></li>
            <li><a href="carrito.php">Carrito de Compras</a></li>
            <li><a href="lista_deseos.php">Lista de deseos</a></li>
            <li><a href="opiniones.php">Opiniones</a></li>
            <li><a href="../logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <h2>Bienvenido, <?php echo $cliente_nombre; ?></h2>
    <p>¡Disfruta tu experiencia de compra!</p>
</body>
</html>
