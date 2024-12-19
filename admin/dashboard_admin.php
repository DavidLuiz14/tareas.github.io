<?php
session_start();
include('../conexion.php');

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'administrador') {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];

// Consultar datos del administrador directamente de la tabla usuarios
$sql = "SELECT nombre_usuario 
        FROM usuarios 
        WHERE nombre_usuario = ? AND rol = 'administrador'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $admin_nombre = htmlspecialchars($admin['nombre_usuario']);
} else {
    echo "No se encontraron datos del administrador.";
    exit();
}

// Mostrar la vista HTML con los datos dinámicos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador</title>
    <link rel="stylesheet" href="../styles/main.css">
</head>
<body>
    <header>
        <h1>Dashboard de Administración</h1>
    </header>
    <nav>
        <ul>
            <li><a href="productos.php">Gestión de Productos</a></li>
            <li><a href="gestion_proveedores.php">Gestión de Proveedores</a></li>
            <li><a href="trabajadores.php">Gestión de Trabajadores</a></li>
            <li><a href="compras.php">Registro de Compras</a></li>
            <li><a href="ventas.php">Registro de Ventas</a></li>
            <li><a href="ver_clientes.php">Listado de Clientes</a></li>
            <li><a href="ver_pedidos.php">Listado de pedidos</a></li>
            <li><a href="../logout.php">Cerrar Sesión</a></li>
        </ul>
    </nav>

    <h2>Bienvenido, <?php echo $admin_nombre; ?></h2>
    <p>Gestiona eficientemente el sistema desde este panel.</p>
</body>
</html>
