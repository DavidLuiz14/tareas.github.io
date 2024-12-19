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

// Funciones para la gestión de opiniones
function obtenerOpiniones($id_cliente) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT o.id_opinion, p.nombre_producto, o.calificacion, o.comentario, o.fecha_opinion
        FROM opiniones o
        JOIN productos p ON o.id_producto = p.id_producto
        WHERE o.id_cliente = ?
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

// Acción de agregar opinión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'agregar') {
    $id_producto = $_POST['id_producto'];
    $calificacion = $_POST['calificacion'];
    $comentario = $_POST['comentario'];

    if ($calificacion >= 1 && $calificacion <= 5 && !empty($comentario)) {
        $stmt = $conn->prepare("INSERT INTO opiniones (id_cliente, id_producto, calificacion, comentario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $id_cliente, $id_producto, $calificacion, $comentario);
        $stmt->execute();
    }

    header('Location: opiniones.php');
    exit();
}

// Acción de eliminar opinión
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id_opinion'])) {
    $id_opinion = $_GET['id_opinion'];
    $stmt = $conn->prepare("DELETE FROM opiniones WHERE id_opinion = ?");
    $stmt->bind_param("i", $id_opinion);
    $stmt->execute();
    header('Location: opiniones.php');
    exit();
}

// Acción de editar opinión
if (isset($_GET['accion']) && $_GET['accion'] === 'editar' && isset($_GET['id_opinion'])) {
    $id_opinion = $_GET['id_opinion'];
    $stmt = $conn->prepare("
        SELECT o.id_opinion, o.calificacion, o.comentario, p.nombre_producto
        FROM opiniones o
        JOIN productos p ON o.id_producto = p.id_producto
        WHERE o.id_opinion = ?
    ");
    $stmt->bind_param("i", $id_opinion);
    $stmt->execute();
    $result = $stmt->get_result();
    $opinion_editar = $result->fetch_assoc();
}

// Acción de actualizar opinión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'actualizar') {
    $id_opinion = $_POST['id_opinion'];
    $calificacion = $_POST['calificacion'];
    $comentario = $_POST['comentario'];

    if ($calificacion >= 1 && $calificacion <= 5 && !empty($comentario)) {
        $stmt = $conn->prepare("UPDATE opiniones SET calificacion = ?, comentario = ? WHERE id_opinion = ?");
        $stmt->bind_param("isi", $calificacion, $comentario, $id_opinion);
        $stmt->execute();
    }

    header('Location: opiniones.php');
    exit();
}

// Obtener opiniones y productos disponibles
$opiniones = obtenerOpiniones($id_cliente);
$productos = obtenerProductos();

// Incluir la vista
include('opiniones.html');
?>
