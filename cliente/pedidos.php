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

// Funciones para la gestión de pedidos
function obtenerPedidos($id_cliente) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM pedidos WHERE id_cliente = ?");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function obtenerDetallesPedido($id_pedido) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM detalles_pedido WHERE id_pedido = ?");
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Acción de eliminar un pedido
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id_pedido'])) {
    $id_pedido = $_GET['id_pedido'];

    // Iniciar transacción
    $conn->begin_transaction();
    try {
        // Eliminar detalles del pedido
        $stmt = $conn->prepare("DELETE FROM detalles_pedido WHERE id_pedido = ?");
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
 
        // Eliminar pedido
        $stmt = $conn->prepare("DELETE FROM pedidos WHERE id_pedido = ?");
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();

        $conn->commit();
        header('Location: pedidos.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error al eliminar el pedido: " . $e->getMessage();
    }
}

// Acción de ver detalles del pedido
if (isset($_GET['accion']) && $_GET['accion'] === 'ver' && isset($_GET['id_pedido'])) {
    $id_pedido = $_GET['id_pedido'];
    $detalles_pedido = obtenerDetallesPedido($id_pedido);
    include('detalles_pedido.php');
    exit();
}

// Obtener pedidos
$pedidos = obtenerPedidos($id_cliente);

// Incluir la vista
include('pedidos.html');
?>
