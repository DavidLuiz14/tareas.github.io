<?php
require('../conexion.php'); 
session_start();


$nombre_usuario = $_SESSION['username'];

if (!$nombre_usuario) {
    header('Location: ../index.php');
    exit();
}

// Obtener ID del cliente
$stmt = $conn->prepare("SELECT id_cliente FROM clientes WHERE nombre_usuario = ?");
$stmt->bind_param("s", $nombre_usuario);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$id_cliente = $cliente['id_cliente'];

// Funciones para la gestión de la lista de deseos
function obtenerListaDeseos($id_cliente) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT ld.id_deseo, p.id_producto, p.nombre_producto, p.precio_venta, ld.fecha_agregado
        FROM lista_deseos ld
        JOIN productos p ON ld.id_producto = p.id_producto
        WHERE ld.id_cliente = ?
    ");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function obtenerProductos() {
    global $conn;
    $result = $conn->query("SELECT * FROM productos");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Acción de agregar producto a la lista de deseos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'agregar') {
    $id_producto = $_POST['id_producto'];

    // Verificar si el producto ya está en la lista de deseos
    $stmt = $conn->prepare("SELECT * FROM lista_deseos WHERE id_cliente = ? AND id_producto = ?");
    $stmt->bind_param("ii", $id_cliente, $id_producto);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Si no está en la lista, agregarlo
        $stmt = $conn->prepare("INSERT INTO lista_deseos (id_cliente, id_producto) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_cliente, $id_producto);
        $stmt->execute();
    }

    header('Location: lista_deseos.php');
    exit();
}

// Acción de eliminar producto de la lista de deseos
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id_deseo'])) {
    $id_deseo = $_GET['id_deseo'];
    $stmt = $conn->prepare("DELETE FROM lista_deseos WHERE id_deseo = ?");
    $stmt->bind_param("i", $id_deseo);
    $stmt->execute();
    header('Location: lista_deseos.php');
    exit();
}

// Acción de agregar producto al carrito desde la lista de deseos
if (isset($_GET['accion']) && $_GET['accion'] === 'agregar_carrito' && isset($_GET['id_producto'])) {
    $id_producto = $_GET['id_producto'];

    // Agregar el producto al carrito
    $stmt = $conn->prepare("INSERT INTO carrito (id_cliente, id_producto, cantidad) VALUES (?, ?, 1)");
    $stmt->bind_param("ii", $id_cliente, $id_producto);
    $stmt->execute();

    // Eliminar el producto de la lista de deseos
    $stmt = $conn->prepare("DELETE FROM lista_deseos WHERE id_cliente = ? AND id_producto = ?");
    $stmt->bind_param("ii", $id_cliente, $id_producto);
    $stmt->execute();

    header('Location: carrito.php');
    exit();
}

// Obtener la lista de deseos y los productos disponibles
$lista_deseos = obtenerListaDeseos($id_cliente);
$productos = obtenerProductos();

// Incluir la vista
include('lista_deseos.html');
?>
