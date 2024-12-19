<?php
require('../conexion.php'); // Archivo de conexión a la base de datos
session_start();

// Obtener el ID del cliente a partir de la sesión
$nombre_usuario = $_SESSION['username'];

// Verificar si el cliente está autenticado
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

// Funciones para la gestión del carrito
function obtenerCarrito($id_cliente) {
    global $conn;
    $stmt = $conn->prepare("
        SELECT c.id_carrito, p.nombre_producto, p.precio_venta, c.cantidad, 
               (p.precio_venta * c.cantidad) AS subtotal
        FROM carrito c
        JOIN productos p ON c.id_producto = p.id_producto
        WHERE c.id_cliente = ?
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

// Acción de agregar al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'agregar') {
    $id_producto = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];

    // Verificar si el producto ya está en el carrito
    $stmt = $conn->prepare("SELECT cantidad FROM carrito WHERE id_cliente = ? AND id_producto = ?");
    $stmt->bind_param("ii", $id_cliente, $id_producto);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Si existe, actualizar la cantidad
        $item = $result->fetch_assoc();
        $nueva_cantidad = $item['cantidad'] + $cantidad;
        $stmt = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id_cliente = ? AND id_producto = ?");
        $stmt->bind_param("iii", $nueva_cantidad, $id_cliente, $id_producto);
    } else {
        // Si no existe, agregar al carrito
        $stmt = $conn->prepare("INSERT INTO carrito (id_cliente, id_producto, cantidad) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $id_cliente, $id_producto, $cantidad);
    }
    $stmt->execute();

    header('Location: carrito.php');
    exit();
}

// Acción de eliminar del carrito
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id_carrito'])) {
    $id_carrito = $_GET['id_carrito'];
    $stmt = $conn->prepare("DELETE FROM carrito WHERE id_carrito = ?");
    $stmt->bind_param("i", $id_carrito);
    $stmt->execute();
    header('Location: carrito.php');
    exit();
}

// Acción de editar la cantidad de un producto en el carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['accion'] === 'editar') {
    $id_carrito = $_POST['id_carrito'];
    $nueva_cantidad = $_POST['cantidad'];
    $stmt = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id_carrito = ?");
    $stmt->bind_param("ii", $nueva_cantidad, $id_carrito);
    $stmt->execute();
    header('Location: carrito.php');
    exit();
}

// Acción de convertir el carrito a un pedido
if (isset($_POST['accion']) && $_POST['accion'] === 'convertir_a_pedido') {
    // Calcular el total del carrito
    $total = 0;
    $stmt = $conn->prepare("SELECT c.cantidad, p.precio_venta FROM carrito c JOIN productos p ON c.id_producto = p.id_producto WHERE c.id_cliente = ?");
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($item = $result->fetch_assoc()) {
        $total += $item['cantidad'] * $item['precio_venta'];
    }

    // Verificar si el total es mayor que 0
    if ($total > 0) {
        // Insertar el pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (id_cliente, total, estado) VALUES (?, ?, 'pendiente')");
        $stmt->bind_param("id", $id_cliente, $total);
        $stmt->execute();
        $id_pedido = $stmt->insert_id;

        // Insertar los detalles del pedido
        $stmt = $conn->prepare("SELECT c.id_carrito, c.id_producto, c.cantidad, p.precio_venta FROM carrito c JOIN productos p ON c.id_producto = p.id_producto WHERE c.id_cliente = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($item = $result->fetch_assoc()) {
            $subtotal = $item['cantidad'] * $item['precio_venta'];
            $stmt_detalle = $conn->prepare("INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt_detalle->bind_param("iiidi", $id_pedido, $item['id_producto'], $item['cantidad'], $item['precio_venta'], $subtotal);
            $stmt_detalle->execute();
        }

        // Vaciar el carrito
        $stmt = $conn->prepare("DELETE FROM carrito WHERE id_cliente = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();

        header('Location: pedidos.php'); // Redirigir a una página de pedidos
        exit();
    } else {
        echo "El total del carrito es 0, no se puede procesar el pedido.";
    }
}

// Obtener el carrito y los productos disponibles
$carrito = obtenerCarrito($id_cliente);
$productos = obtenerProductos();

// Calcular el total del carrito
$total = array_sum(array_column($carrito, 'subtotal'));

// Incluir la vista
include('carrito.html');
?>
