<?php
require('../conexion.php'); 


// Obtener ID del cliente
$stmt = $conn->prepare("SELECT id_cliente FROM clientes WHERE nombre_usuario = ?");
$stmt->bind_param("s", $nombre_usuario);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$id_cliente = $cliente['id_cliente'];

// Obtener detalles del pedido
if (isset($_GET['id_pedido'])) {
    $id_pedido = $_GET['id_pedido'];
    $stmt = $conn->prepare("SELECT dp.id_producto, p.nombre_producto, dp.cantidad, dp.precio_unitario, dp.subtotal 
                            FROM detalles_pedido dp 
                            JOIN productos p ON dp.id_producto = p.id_producto 
                            WHERE dp.id_pedido = ?");
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    $detalles_pedido = $result->fetch_all(MYSQLI_ASSOC);

    // Obtener total del pedido
    $stmt = $conn->prepare("SELECT total FROM pedidos WHERE id_pedido = ?");
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $result = $stmt->get_result();
    $pedido = $result->fetch_assoc();
    $total_pedido = $pedido['total'];
} else {
    echo "ID de pedido no válido.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Pedido</title>
    <link rel="stylesheet" href="../styles/modulos.css">
</head>
<body>
    <header>
        <h1>Detalles del Pedido</h1>
        <nav>
            <a href="pedidos.php">Volver a Mis Pedidos</a>
        </nav>
    </header>
    <main>
        <section class="detalles-pedido">
            <h2>Pedido ID: <?= $id_pedido ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles_pedido as $detalle): ?>
                        <tr>
                            <td><?= $detalle['nombre_producto'] ?></td>
                            <td><?= $detalle['cantidad'] ?></td>
                            <td>$<?= number_format($detalle['precio_unitario'], 2) ?></td>
                            <td>$<?= number_format($detalle['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><strong>Total del Pedido: $<?= number_format($total_pedido, 2) ?></strong></p>
        </section>
    </main>
</body>
</html>
