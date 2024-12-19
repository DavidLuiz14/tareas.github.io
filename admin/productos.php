<?php
require('../conexion.php');

session_start();

$nombre_usuario = $_SESSION['username'];

if (!$nombre_usuario) {
    header('Location: ../index.php');
    exit();
}

// Variables para almacenar productos
$productos = [];
$producto = null;

// Acción de agregar/editar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del formulario
    $id_producto = $_POST['id_producto'] ?? null;
    $nombre_producto = $_POST['nombre_producto'];
    $tipo_producto = $_POST['tipo_producto']; 
    $descripcion = $_POST['descripcion'];
    $precio_compra = $_POST['precio_compra'];
    $precio_venta = $_POST['precio_venta'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];

    if ($_POST['accion'] === 'agregar') {
        // Insertar producto
        $stmt = $conn->prepare("
            INSERT INTO productos (nombre_producto, tipo_producto, descripcion, precio_compra, precio_venta, stock_actual, stock_minimo) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssdiii", $nombre_producto, $tipo_producto, $descripcion, $precio_compra, $precio_venta, $stock_actual, $stock_minimo);
        $stmt->execute();
    } elseif ($_POST['accion'] === 'editar') {
        // Editar producto
        $stmt = $conn->prepare("
            UPDATE productos 
            SET nombre_producto = ?, tipo_producto = ?, descripcion = ?, precio_compra = ?, precio_venta = ?, stock_actual = ?, stock_minimo = ? 
            WHERE id_producto = ?
        ");
        $stmt->bind_param("sssdiiii", $nombre_producto, $tipo_producto, $descripcion, $precio_compra, $precio_venta, $stock_actual, $stock_minimo, $id_producto);
        $stmt->execute();
    }

    // Redirigir al listado de productos después de agregar o editar
    header('Location: productos.php');
    exit();
}

// Acción de eliminar producto
if (isset($_GET['delete'])) {
    $id_producto = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();

    // Redirigir al listado de productos después de eliminar
    header('Location: productos.php');
    exit();
}

// Acción de cargar un producto para edición
if (isset($_GET['edit'])) {
    $id_producto = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();
}

// Obtener todos los productos
$result = $conn->query("SELECT * FROM productos");
if ($result) {
    $productos = $result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();

// Incluir el archivo HTML
include('productos.html'); 
?>
